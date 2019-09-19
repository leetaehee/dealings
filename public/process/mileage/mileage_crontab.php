<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 
	 *	1. 마일리지 유효기간 지날 시 삭제 (imi_mileage_charge.is_expiration='Y')
	 *  2. 크론탭에서만 실행 할것
	 *	3. 추후 크론탭 아닌곳에서 실행 시 접근못하도록 수정 할 것
	 */

	$topDir = __DIR__.'/../../..';

	include_once $topDir . '/configs/config.php'; // 환경설정
	include_once $topDir . '/messages/message.php'; // 메세지
	include_once $topDir . '/includes/function.php'; // 공통함수

	include_once $topDir . '/adodb/adodb.inc.php'; // adodb
	include_once $topDir . '/includes/adodbConnection.php'; // adodb

	include_once $topDir . '/class/MileageClass.php'; // Class 파일
	include_once $topDir . '/class/MemberClass.php'; // Class 파일

    try {
        /*
         * -- imi_mileage_charge 테이블에서 아래 조건인 Row만 추출 (order by 줄것)
         * 1. is_expiration=N
         * 2. mileage_idx <> 5 (가상결제가 아니면)
         * 3. charge_status = 3,6
         * 4. expiration_date < 오늘날짜보다 작은 경우 
         * -- is_expiration ='Y', imi_mileage_charge.charge_stauts=4(유효기간초과)로 할것
         * -- 사용마일리지, 남은 마일리지 조정
         * -- imi_mileage_change에 변경이력 넣기
         * -- imi_members.mileage 수정
         * -- imi_mileage_type_sum 업데이트 (가상계좌 제외하고 넣을 것)
         */

        $today = date('Y-m-d');
        $errorMessage = '';
        $notErrorMessage = '';

        $db->beginTrans(); // 트랜잭션시작

        $mileageClass = new MileageClass($db);
        $memberClass = new MemberClass($db);

        $excessValidDateList = $mileageClass->getMileageExcessValidDateList();
        $excessDataCount = $excessValidDateList->recordCount();
        if ($excessDataCount > 0) {
            // 결제내역 데이터를 배열로 변환하기
            $chargeData = $mileageClass->getExpirationArrayData($excessValidDateList);
            if ($chargeData === false) {
                throw new Exception('데이터를 배열로 저장할 때 오류 발생!');
            }

            // 유효기간 만료로 충전내역 변경
            $updateChargeResult = $mileageClass->updateExpirationDate($chargeData);
            if ($updateChargeResult < 1) {
                throw new Exception('만료된 데이터가 없습니다.');
            }

            // 변동내용 추가
            $insertChangeResult = $mileageClass->insertMileageChange($chargeData);
            if ($insertChangeResult < 1) {
                throw new Exception('마일리지 변동내역 추가하다가 오류 발생!');
            }

             // 전체 마일리지 조회
            $memberTotalMileage = $mileageClass->getAllMemberMileageTotal();
            if ($memberTotalMileage == false) {
                throw new Exception('마일리지 조회 중에 오류 발생!');
            }

            // 개별정보수정
            $updateResult = $memberClass->updateAllMemberMilege($memberTotalMileage);
            if ($updateResult < 1) {
                throw new Exception('회원 마일리지 수정 실패!');
            }
            
            $mileageTypeData = $mileageClass->getAllMemberPartMileageTotal();
            if ($mileageTypeData == false) {
                throw new Exception('회원 마일리지 유형별 합계 조회 오류!');
            }

            // 마일리지 타입별로 수정하기 
            $typeCount = $mileageTypeData->recordCount();
            if ($typeCount > 0) {
                foreach ($mileageTypeData as $key => $value) {
                    $mileageIdx = $value['mileage_idx'];
                    $chargeCost = $value['charge_cost'];
                    $memberIdx = $value['member_idx'];

                    $idx = $mileageClass->getMemberMileageTypeIdx($memberIdx);

                    if (empty($idx)) {
                        $param = [
                            'member_idx' => $memberIdx,
                            'charge_idx' => $chargeCost
                        ];

                        $insertResult = $mileageClass->mileageTypeInsert($mileageIdx, $param);
                        if ($insertResult < 1) {
                            throw new Exception('memberIdx: '.$memberIdx.'번, 데이터 입력 오류 발생!');
                        } else {
                            $notErrorMessage = '회원별 마일리지 타입에 대해 추가 완료';
                        }
                    } else {
                        $param = [
                            'charge_idx' => $chargeCost,
                            'member_idx' => $memberIdx
                        ];

                        $updateResult = $mileageClass->mileageTypeWithdrawalUpdate($mileageIdx, $param);
                        if ($updateResult < 1) {
                            throw new Exception('memberIdx: '.$memberIdx.'번, 데이터 수정 오류 발생!');
                        } else {
                            $notErrorMessage = '회원별 마일리지 타입에 대해 수정 완료';
                        }
                    }
                }

                $chargeExpirationParam = [
                        'is_expiration'=>'Y',
                        'charge_status'=>4,
                        'expiration_date'=>$today
                    ];

                $updateChargeExpiration = $mileageClass->updateStatusByExpirationDate($chargeExpirationParam);
                if ($updateChargeExpiration < 1) {
                    $notErrorMessage = '> 충전상태 변경 오류! 관리자에게 문의하세요. <br>';
                    throw new Exception($errorMessage);
                }
                $db->commitTrans();
            } else {
                throw new Exception('회원별 마일리 타입 데이터 가져오는 중에 오류 발생!');
            }
        } else {
            // 유효기간 만료된 내역 추출하기 
            if ($excessValidDateList === false) {
                throw new Exception('관리자에게 문의하세요');
            } else {
                throw new Exception('데이터가 존재하지 않습니다.');
            }
        }
    } catch (Exception $e) {
        if ($connection === true) {
			$errorMessage = $e->getMessage();
            $db->rollbackTrans();
        }
    } finally {
        if ($connection === true) {
            $db->completeTrans();
            $db->close();
        }

		if (!empty($errorMessage)) {
            echo $errorMessage;   
        }
        
        if (!empty($notErrorMessage)) {
            echo $notErrorMessage;   
        }
    }