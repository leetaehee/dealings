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
	
	// adodb
	include_once $topDir . '/adodb/adodb.inc.php';
	include_once $topDir . '/includes/adodbConnection.php';
	
	// Class 파일
	include_once $topDir . '/class/MileageClass.php';
	include_once $topDir . '/class/MemberClass.php';

	// Exception 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../Exception/RollbackException.php';

    try {
        $today = date('Y-m-d');
		$message = '';

        $db->beginTrans(); // 트랜잭션시작

        $mileageClass = new MileageClass($db);
        $memberClass = new MemberClass($db);

        $excessValidDateList = $mileageClass->getMileageExcessValidDateList();
        $excessDataCount = $excessValidDateList->recordCount();
        if ($excessDataCount > 0) {
            // 결제내역 데이터를 배열로 변환하기
            $chargeData = $mileageClass->getExpirationArrayData($excessValidDateList);
            if ($chargeData === false) {
                throw new RollbackException('데이터를 배열로 저장 시 오류 발생했습니다. |');
            }

            // 유효기간 만료로 충전내역 변경
            $updateChargeResult = $mileageClass->updateExpirationDate($chargeData);
            if ($updateChargeResult < 1) {
                throw new RollbackException('만료된 데이터가 없습니다. |');
            }

            // 변동내용 추가
            $insertChangeResult = $mileageClass->insertMileageChange($chargeData);
            if ($insertChangeResult < 1) {
                throw new RollbackException('마일리지 변동내역 추가 오류 발생했습니다. |');
            }

             // 전체 마일리지 조회
            $memberTotalMileage = $mileageClass->getAllMemberMileageTotal();
            if ($memberTotalMileage == false) {
                throw new RollbackException('마일리지 조회 오류 발생 했습니다. |');
            }

            // 개별정보수정
            $updateResult = $memberClass->updateAllMemberMilege($memberTotalMileage);
            if ($updateResult < 1) {
                throw new RollbackException('회원 마일리지 수정 실패 했습니다. |');
            }
            
            $mileageTypeData = $mileageClass->getAllMemberPartMileageTotal();
            if ($mileageTypeData == false) {
                throw new RollbackException('회원 마일리지 유형별 합계 조회 오류 발생했습니다. |');
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
                            throw new RollbackException('memberIdx: '.$memberIdx.'번 입력 오류 발생했습니다. |');
                        } else {
                            $message = '회원별 마일리지 타입에 대해 추가 완료 |';
                        }
                    } else {
                        $param = [
                            'charge_idx' => $chargeCost,
                            'member_idx' => $memberIdx
                        ];

                        $updateResult = $mileageClass->mileageTypeWithdrawalUpdate($mileageIdx, $param);
                        if ($updateResult < 1) {
                            throw new RollbackException('memberIdx: '.$memberIdx.'번 수정 오류 발생했습니다. |');
                        } else {
                            $message = '회원별 마일리지 타입에 대해 수정 완료 |';
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
                    throw new RollbackException('충전상태 변경 오류! 관리자에게 문의하세요. |');
                }

				$alertMessage = '유효기간 만료데이터를 정상적으로 삭제하였습니다.';
				$db->commitTrans();
				$db->completeTrans();
            } else {
                throw new RollbackException('회원별 마일리 타입 데이터 가져오는 중에 오류가 발생했습니다. |');
            }
        } else {
            // 유효기간 만료된 내역 추출하기 
            if ($excessValidDateList === false) {
                throw new RollbackException('관리자에게 문의하세요');
            } else {
                throw new RollbackException('데이터가 존재하지 않습니다.');
            }
        }
    } catch (RollbackException $e) {
		// 트랜잭션 문제가 발생했을 때
		$alertMessage = $e->errorMessage();
		$db->rollbackTrans();
	} catch (Exception $e) {
		// 트랜잭션을 사용하지 않을 때
		$alertMessage = $e->getMessage();
    } finally {
        if  ($connection === true) {
            $db->close();
        }
		
        if (!empty($alertMessage)) {
            echo $alertMessage.'<br>';
			echo $message;
		}
    }