<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 마일리지 (충전, 취소, 출금 등)
	 */

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; // 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; // 공통함수

	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

	// Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MileageClass.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MemberClass.php';

	// Exception 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../Exception/RollbackException.php';

    try {
        $returnUrl = SITE_DOMAIN; // 리턴되는 화면 URL 초기화.
        $alertMessage = '';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

        if (isset($_POST['mode'])) {
            $mode = htmlspecialchars($_POST['mode']);
        } else {
            $mode = htmlspecialchars($_GET['mode']);
        }
        
        /*
         * 마일리지 구분- 가상계좌(5), 문화상품권(3), 휴대전화(2), 신용카드(1)
         */
        if ($mode == 'charge') {
            /*
             * 가상계좌 충전
             */
            $chargeDate = date('Y-m-d');

            // injection, xss 방지코드
            $_POST['mileage_type'] = htmlspecialchars($_POST['mileage_type']); 
            $_POST['account_bank'] = htmlspecialchars($_POST['account_bank']);
            $_POST['account_no'] = htmlspecialchars($_POST['account_no']);
            $_POST['charge_cost'] = htmlspecialchars($_POST['charge_cost']);
            $_POST['charge_name'] = htmlspecialchars($_POST['charge_name']);
            $postData = $_POST;
            
            $mileageType = $postData['mileage_type'];
            $idx = $_SESSION['idx'];

            $mileageClass = new MileageClass($db);
            $memberClass = new MemberClass($db);
            $resultMileageValidCheck = $mileageClass->checkChargeFormValidate($postData);

            // 유효성 검사 실패시 마일리지 타입별 링크
            if ($mileageType == 5) {
                $returnUrl = SITE_DOMAIN.'/virtual_account_charge.php'; 
            } else if ($mileageType == 1) {
                $returnUrl = SITE_DOMAIN.'/card_charge.php'; 
            } else if ($mileageType == 3) {
                $returnUrl = SITE_DOMAIN.'/voucher_charge.php'; 
            } else if ($mileageType == 2) {
                $returnUrl = SITE_DOMAIN.'/phone_charge.php'; 
            }

            if($resultMileageValidCheck['isValid'] === false) {
                // 폼 데이터 받아서 유효성 검증
                throw new Exception($resultMileageValidCheck['errorMessage']);
            }

            // 트랜잭션 처리 할것!
            $db->beginTrans();

            $expirationData = $mileageClass->getExpirationDay($mileageType); //유효기간 만료일 구하기
            if ($expirationData === false) {
                throw new RollbackException('마일리지 만료정보 가져오는데 실패했습니다.');
            } else {
                $expirationDate = '';
                if ($expirationData['period'] != 'none') {
                    // 유효기간 만료일자 지정
                    $period = "+".$expirationData['day'].' '.$expirationData['period'];
                    $expirationDate = date('Y-m-d',strtotime($period,strtotime($chargeDate)));
                }

                $chargeParam = [
                        'idx'=>$idx,
                        'account_bank'=>$postData['account_bank'],
                        'account_no'=>setEncrypt($postData['account_no']),
                        'charge_cost'=>$postData['charge_cost'],
                        'spare_cost'=>$postData['charge_cost'],
                        'charge_name'=>$postData['charge_name'],
                        'mileageType'=>$mileageType,
                        'chargeDate'=>$chargeDate,
                        'charge_status'=>3,
                    ];

                if (!empty($expirationDate)) {
                    $chargeParam['expirationDate'] = $expirationDate;
                }

                $insertChargeResult = $mileageClass->insertMileageCharge($chargeParam); // 충전정보 추가
                if ($insertChargeResult < 1) {
                    throw new RollbackException('마일리지 충전 실패했습니다.');
                }

                $mileageParam = [
                    'charge_cost'=>$postData['charge_cost'],
                    'idx'=>$idx
                ];

                $updateResult = $memberClass->updateMileageCharge($mileageParam); // 마일리지변경
                if ($updateResult < 1) {
                    throw new RollbackException('마일리지 충전 변경 실패했습니다.');
                }

                $memberMileageType = $mileageClass->getMemberMileageTypeIdx($idx);

                if ($memberMileageType == false) {
                    $mileageTypeParam = [
                        $idx, 
                        $postData['charge_cost']
                    ];
                    $mileageTypeInsert = $mileageClass->mileageTypeInsert($mileageType, $mileageTypeParam);
                    if ($mileageTypeInsert < 1) {
                        throw new RollbackException('마일리지 유형별 합계 삽입 실패했습니다.');
                    }
                } else {
                    $mileageTypeParam = [
                        $postData['charge_cost'],
                        $idx
                    ];
                    $mileageTypeUpdate = $mileageClass->mileageTypeChargeUpdate($mileageType, $mileageTypeParam);
                    if ($mileageTypeUpdate < 1) {
                        throw new RollbackException('마일리지 유형별 합계 변동 실패했습니다.');
                    }
                }
				$returnUrl = SITE_DOMAIN.'/my_charge_list.php';

				$db->commitTrans();
				$alertMessage = '마일리지가 충전되었습니다. 감사합니다.';
            }
        } else if ($mode == 'withdrawal') {
            /*
             * 가상계좌 출금
             */
            $processDate = date('Y-m-d');

            // injection, xss 방지코드
            $_POST['mileage_type'] = htmlspecialchars($_POST['mileage_type']); 
            $_POST['account_bank'] = htmlspecialchars($_POST['account_bank']);
            $_POST['account_no'] = htmlspecialchars($_POST['account_no']);
            $_POST['charge_name'] = htmlspecialchars($_POST['charge_name']);
            $_POST['charge_cost'] = htmlspecialchars($_POST['charge_cost']);
            $postData = $_POST;

            $idx = $_SESSION['idx'];
            $mileageType = (int)$postData['mileage_type'];

            // 유효성 검사 실패시 마일리지 타입별 링크
            if ($mileageType == 5) {
                $returnUrl = SITE_DOMAIN.'/virtual_account_mileage_withdrawal.php';
            }

            $mileageClass = new MileageClass($db);
            $memberClass = new MemberClass($db);

            $resultMileageValidCheck = $mileageClass->checkChargeFormValidate($postData);
            if( $resultMileageValidCheck['isValid'] === false) {
                // 폼 데이터 받아서 유효성 검증
                throw new Exception($resultMileageValidCheck['errorMessage']);
            }

            $maxMileageParam = [
                'mileageType'=>$mileageType,
                'idx'=>$idx
            ];
			
			// 트랜잭션시작
			$db->beginTrans();

            $maxMileage = $mileageClass->getAvailableMileage($maxMileageParam);
            if ($maxMileage === false) {
                throw new RollbackException('출금 가능한 마일리지 가져오는 중에 오류 발생! 관리자에게 문의하세요!');
            }

            if ($postData['charge_cost'] > $maxMileage) {
                throw new RollbackException('출금금액이 출금가능금액을 초과합니다!');
            }

            $param = [
				'mileage_idx'=>$mileageType,
				'member_idx'=>$idx,
				'charge_status'=>3
			];
            if ($mileageType === 5) {
                // 충전가능한 내역 리스트
                $virtualWitdrawaLlist = $mileageClass->getVirutalAccountWithdrawalPossibleList($param);
                if ($virtualWitdrawaLlist === false) {
                    throw new RollbackException('충전 내역을 가져오는 중에 오류가 발생하였습니다.');
                }
            }

            //imi_mileage_charge 수량 변경을 위한 정보 얻어오기
            $pChargeData = $postData['charge_cost']; 
            $chargeArray = $mileageClass->getMildateChargeInfomationData($virtualWitdrawaLlist, $pChargeData);
            if ($chargeArray === false) {
                throw new RollbackException('수량정보 데이터를 가져오는 중에 오류가 발생하였습니다.');
            }
			$chargeData = $chargeArray['update_data'];

            $updateChargeResult = $mileageClass->updateMileageCharge($chargeData);
            if ($updateChargeResult === false) {
                throw new RollbackException('출금 수정 실패! 관리자에게 문의하세요');
            }

            // 트랜잭션 테스트 시 아래 제거 후 catch문에서 잘잡는지 확인
            $spareZeroCount = $mileageClass->getCountChargeSpareCountZero();
            if ($spareZeroCount < 0) {
                throw new RollbackException('마일리지 상태 조회 오류! 관리자에게 문의하세요');
            }

            if ($spareZeroCount > 0) {
                $updateZeroResult = $mileageClass->updateChargeZeroStatus();
                if ($updateZeroResult === false) {
                    throw new RollbackException('마일리지 출금 상태 변경 실패! 관리자에게 문의하세요');
                }
            }

            $mileageChangeParam = [
				'memberIdx'=>$idx,
				'mileageIdx'=>$mileageType,
				'accountNo'=>$postData['account_no'],
				'accountBank'=>$postData['account_bank'],
				'chargeName'=>$postData['charge_name'],
				'chargeStatus'=>2,
				'process_date'=>date('Y-m-d')
			];
            $changeData = $mileageClass->updateMileageArray($chargeData, $mileageChangeParam);
			if ($changeData === false) {
				throw new RollbackException('배열 데이터 생성 오류');
			}

            // 출금데이터 생성
            $insertChangeResult = $mileageClass->insertMileageChange($changeData);
            if ($insertChangeResult < 1) {
                throw new RollbackException('출금데이터 생성 실패! 관리자에게 문의하세요');
            }

            $mileageParam = [
                'mileage'=>$postData['charge_cost'],
                'idx'=>$idx
            ];

            $updateResult = $memberClass->updateMileageWithdrawal($mileageParam); // 마일리지변경
            if ($updateResult < 1) {
                throw new RollbackException('회원 마일리지 수정 실패! 관리자에게 문의하세요');
            }

            $mileageTypeParam = [
                $postData['charge_cost'], 
                $idx
            ];
            $mileageTypeUpdate = $mileageClass->mileageTypeWithdrawalUpdate($mileageType, $mileageTypeParam);
            if ($mileageTypeUpdate < 1) {
                throw new RollbackException('마일리지 유형별 출금금액 수정 오류가 발생했습니다.');
            } else {
                $returnUrl = SITE_DOMAIN.'/my_withdrawal_list.php';
			
				$db->commitTrans();
                $alertMessage = '출금이 완료되었습니다! 감사합니다';
            }
        } else if ($mode == 'mileage_cancel') {
            // 충전정보, 변동내역, 회원정보, 마일리지타입정보 모두 바꾸기
            $returnUrl = SITE_ADMIN_DOMAIN.'/charge_list.php'; // 작업이 끝나고 이동하는 URL
            
            // xss, inject 방지 코드
            $chargeIdx = isset($_GET['idx']) ? htmlspecialchars($_GET['idx']) : '';
            if (empty($chargeIdx)) {
                throw new Exception('잘못된 접근입니다! 관리자에게 문의하세요.');
            }

            $mileageClass = new MileageClass($db);
            $memberClass = new MemberClass($db);

            $db->beginTrans(); // 트랜잭션시작

            $chargeData = $mileageClass->getChargeInsertData($chargeIdx);
            if ($chargeData === false) {
                throw new RollbackException('충전정보를 가져오는 중에 오류가 발생했습니다.');
            }

            $statusParam = [1, 0, 0, $chargeIdx]; // 충전취소는 남은금액은 삭감시키지않는다.
            $updateStatusResult = $mileageClass->updateChargeStatus($statusParam);
            if ($updateStatusResult < 1) {
                throw new RollbackException('충전내역 상태를 변경하는 중에 오류가 발생했습니다.');
            }

            $mileageChangeParam[] = [
                    'member_idx'=>$chargeData->fields['member_idx'],
                    'mileage_idx'=>$chargeData->fields['mileage_idx'],
                    'accountNo'=>$chargeData->fields['charge_account_no'],
                    'accountBank'=>$chargeData->fields['charge_infomation'],
                    'chargeName'=>$chargeData->fields['charge_name'],
                    'chargeStatus'=>1,
                    'process_date'=>date('Y-m-d'),
                    'charge_idx'=>$chargeData->fields['idx'],
                    'charge_cost'=>$chargeData->fields['charge_cost'],
                ];
            $mileageType = $chargeData->fields['mileage_idx'];

            // 출금데이터 생성
            $insertChangeResult = $mileageClass->insertMileageChange($mileageChangeParam);
            if ($insertChangeResult < 1) {
                throw new RollbackException('출금데이터 생성 실패했습니다.');
            }

            $mileageParam = [
                    'charge_cost'=>$chargeData->fields['charge_cost'],
                    'idx'=>$chargeData->fields['member_idx']
                ];

            $updateResult = $memberClass->updateMileageWithdrawal($mileageParam); // 마일리지변경
            if ($updateResult < 1) {
                throw new RollbackException('회원 마일리지 수정 실패 실패했습니다.');
            }

            $mileageTypeParam = [
                'charge_cost'=>$chargeData->fields['charge_cost'],
                'idx'=>$chargeData->fields['member_idx']
            ];
            $mileageTypeUpdate = $mileageClass->mileageTypeWithdrawalUpdate($mileageType, $mileageTypeParam);
            if ($mileageTypeUpdate < 1) {
                throw new RollbackException('마일리지 유형별 출금 합계 수정 오류가 발생했습니다.');
            } else {
                $db->commitTrans();
				$alertMessage = '충전이 취소 되었습니다.';
            }
        }
		$db->completeTrans();
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
            alertMsg($returnUrl, 1, $alertMessage);
        } else {
            alertMsg(SITE_DOMAIN, 0);
        }
    }