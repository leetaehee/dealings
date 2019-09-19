<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 상품권 거래 등록, 판매, 구매, 삭제 기능을 수행한다. 
	 */

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; // 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; // 공통함수
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/mailer.lib.php'; // PHP메일보내기

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php'; // adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php'; // adodb

    // Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/DealingsClass.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MileageClass.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MemberClass.php';

	try {
		// 리턴되는 화면 URL 초기화.
        $returnUrl = SITE_DOMAIN.'/voucher_dealings.php';
        $alertMessage = ''; // 메세지
        
        if ($connection === false) {
            $alertMessage = '데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요';
            throw new Exception($alertMessage);
        }

        if (isset($_POST['mode'])) {
            $mode = htmlspecialchars($_POST['mode']);
        } else {
            $mode = htmlspecialchars($_GET['mode']);
        }

		if ($mode == 'enroll') {
            $dealingsClass = new DealingsClass($db);

			// injection, xss 방지코드
			$_POST['dealings_state'] = htmlspecialchars($_POST['dealings_state']);
			$_POST['dealings_type'] = htmlspecialchars($_POST['dealings_type']);
			$_POST['dealings_subject'] = htmlspecialchars($_POST['dealings_subject']);
			$_POST['dealings_content'] = htmlspecialchars($_POST['dealings_content']);
			$_POST['item_no'] = htmlspecialchars($_POST['item_no']);
			$_POST['item_money'] = htmlspecialchars($_POST['item_money']);
			$_POST['dealings_mileage'] = htmlspecialchars($_POST['dealings_mileage']);
			$_POST['memo'] = htmlspecialchars($_POST['memo']);
			$_POST['commission'] = htmlspecialchars($_POST['commission']);

			$itemObjectNo = null;
			if (isset($_POST['item_object_no'])) {
				$_POST['item_object_no'] = htmlspecialchars($_POST['item_object_no']);
				if (isset($_POST['item_object_no'])) {
					$itemObjectNo = setEncrypt($_POST['item_object_no']);
				}
			}
			$postData = $_POST;

			// 폼 데이터 받아서 유효성 검증 실패시 리다이렉트 경로
			if ($postData['dealings_state'] == '구매대기') {
				$returnUrl = SITE_DOMAIN.'/voucher_purchase_enroll.php';
			} else if ($postData['dealings_state'] == '판매대기') {
				$returnUrl = SITE_DOMAIN.'/voucher_sell_enroll.php';
			}

			$resultDealingsValidCheck = $dealingsClass->checkDealingFormValidate($postData);
            if ($resultDealingsValidCheck['isValid'] == false) {
				// 유효성 오류
                throw new Exception($resultDealingsValidCheck['errorMessage']);
            } else {	
				$today = date('Y-m-d');
				$expiration_date = date('Y-m-d',strtotime('+5 day',strtotime($today))); // 만료일
				
				// 트랜잭션시작
                $db->beginTrans();

				// 거래상태 가져오기
				$dealingsStatus = $dealingsClass->getDealingsStatus($postData['dealings_state']);
				if ($dealingsStatus === false) {
					throw new Exception('거래상태 오류 발생! 관리자에게 문의하세요.');
				}

				// 거래상태 코드 변환 
				$insertData = [
					'expiration_date'=>$expiration_date,
					'register_date'=>$today,
					'dealings_type'=>$postData['dealings_type'],
					'dealings_subject'=>$postData['dealings_subject'],
					'dealings_content'=>$postData['dealings_content'],
					'item_no'=>$postData['item_no'],
					'item_money'=>$postData['item_money'],
					'item_object_no'=>$itemObjectNo,
					'dealings_mileage'=>$postData['dealings_mileage'],
					'dealings_commission'=>$postData['commission'],
					'dealings_status'=>$dealingsStatus,
					'memo'=>$postData['memo'],
					'idx'=>$_SESSION['idx']
				];

				$insertResult = $dealingsClass->insertDealings($insertData);
				if ($insertResult < 1) {
					throw new Exception('거래데이터 생성 실패! 관리자에게 문의하세요');
				} else {
					$processData = [
						'dealings_idx'=>$insertResult,
						'dealings_status_idx'=>$dealingsStatus
					];

					$insertProcessResult = $dealingsClass->insertDealingsProcess($processData);
					if ($insertProcessResult < 1) {
						throw new Exception('거래 처리과정 생성 실패! 관리자에게 문의하세요');
					} else {
						$returnUrl = SITE_DOMAIN.'/voucher_dealings.php';
						$alertMessage = '정상적으로 거래글이 등록되었습니다.';
					}
				}
				$db->commitTrans();
			}
		} else if ($mode == 'changeStatus') {
			// 상태만 바꿈
			$dealingsClass = new DealingsClass($db);

			$_POST['dealings_idx'] = htmlspecialchars($_POST['dealings_idx']);
			$_POST['dealings_writer_idx'] = htmlspecialchars($_POST['dealings_writer_idx']);
			$_POST['dealings_status'] = htmlspecialchars($_POST['dealings_status']);
			$_POST['dealings_type'] = htmlspecialchars($_POST['dealings_type']);
			$postData = $_POST;

			if ($postData['dealings_type'] === '구매'){
				$returnUrl = SITE_DOMAIN.'/voucher_purchase_enroll.php';
			} else if ($postData['dealings_type'] === '판매') {
				$returnUrl = SITE_DOMAIN.'/voucher_purchase_enroll.php';
			} else {
				$returnUrl = SITE_DOMAIN.'/voucher_purchase_enroll.php';
			}

			$param = [
				'idx'=>$postData['dealings_idx'],
				'is_del'=>'N',
				'dealinges_status'=>$postData['dealings_status'],
			];

			// 트랜잭션시작
            $db->beginTrans();

			$existCount = $dealingsClass->isDealingsDataExist($param);
			if ($existCount === false){
				throw new Exception('거래 데이터 이중등록 select 오류! 관리자에게 문의하세요.');
			} else {
				if($existCount === 0){
					throw new Exception('해당 데이터는 대기상태가 아닙니다!');
				}
			}

			// 다음 거래상태 구하기
			$statusData = [
				'dealings_status'=>$postData['dealings_status']
			];

			$nextStatus = $dealingsClass->getNextDealingsStatus($statusData);
			if ($nextStatus === false) {
				throw new Exception('거래 상태 오류! 관리자에게 문의하세요.');
			}

			$dealingsExistCount = $dealingsClass->isExistDealingsIdx($postData['dealings_idx']);
			if ($dealingsExistCount === false) {
				throw new Exception('거래 유저 테이블 오류! 관리자에게 문의하세요.');
			}
	
			if ($postData['dealings_status'] == 1 && $dealingsExistCount == 0) {
				// 판매대기, 구매대기의 경우 거래유저테이블에 데이터 생성	
				$userData = [
					'dealings_idx'=>$postData['dealings_idx'],
					'dealings_writer_idx'=>$postData['dealings_writer_idx'],
					'dealings_member_idx'=>$_SESSION['idx'],
					'dealings_status'=>$nextStatus,
					'dealings_type'=>$postData['dealings_type']
				];

				$insertResult = $dealingsClass->insertDealingsUser($userData);
				if ($insertResult < 1) {
					throw new Exception('거래 유저 생성 오류! 관리자에게 문의하세요');
				}
			} else {
				// 거래유저테이블에 데이터 수정
				$userData = [
					'dealings_status'=>$nextStatus,
					'dealings_idx'=>$postData['dealings_idx']
				];

				$updateResult = $dealingsClass->updateDealingsUser($userData);
				if ($updateResult < 1) {
					throw new Exception('거래 유저 수정 오류! 관리자에게 문의하세요');
				}
			}

			$dealingsParam = [
				'nextStatus'=>$nextStatus,
				'idx'=>$postData['dealings_idx']
			];
			
			// 거래테이블 상태변경 
			$updateDealingsStatus = $dealingsClass->updateDealingsStatus($dealingsParam);
			if ($updateDealingsStatus < 1) {
				throw new Exception('거래 상태 정보 수정 오류! 관리자에게 문의하세요');
			}

			// 처리절차 생성하기
			$processData = [
				'dealings_idx'=>$postData['dealings_idx'],
				'dealings_status_idx'=>$nextStatus
			];

			$insertProcessResult = $dealingsClass->insertDealingsProcess($processData);
			if ($insertProcessResult < 1) {
				throw new Exception('거래 처리과정 생성 실패! 관리자에게 문의하세요');
			} else {
				$db->commitTrans();
				$returnUrl = SITE_DOMAIN.'/voucher_dealings.php';
				$alertMessage = '정상적으로 거래 상태가 변경되었습니다.';
			}
		} else if ($mode == 'changeCancelStatus') {
			//판매취소 
			$dealingsClass = new DealingsClass($db);

			$_POST['dealings_idx'] = htmlspecialchars($_POST['dealings_idx']);
			$_POST['dealings_writer_idx'] = htmlspecialchars($_POST['dealings_writer_idx']);
			$_POST['dealings_status'] = htmlspecialchars($_POST['dealings_status']);
			$_POST['dealings_type'] = htmlspecialchars($_POST['dealings_type']);
			$postData = $_POST;
			
			$param = '?type='.$postData['dealings_status'].'&idx='.$postData['dealings_idx'];
			$returnUrl = SITE_DOMAIN.'/my_sell_dealings_status.php'.$param;

			// 트랜잭션시작
            $db->beginTrans();

			// 거래 데이터 상태 수정
			$dealingsParam = [
				'dealings_status'=>1,
				'idx'=>$postData['dealings_idx']
			];

			$updateDealingsStatusResult = $dealingsClass->updateDealingsStatus($dealingsParam);
			if ($updateDealingsStatusResult < 1) {
				throw new Exception('거래 데이터 테이블 오류 발생! 관리자에게 문의하세요');
			}

			// 거래유저정보 상태 수정
			$userData = [
				'dealings_status'=>1,
				'dealings_idx'=>$postData['dealings_idx']
			];

			$updateDealingsUserResult = $dealingsClass->updateDealingsUser($userData);
			if ($updateDealingsUserResult < 1) {
				throw new Exception('거래 유저 수정 오류! 관리자에게 문의하세요');
			}

			// 처리절차 생성하기
			$processData = [
				'dealings_idx'=>$postData['dealings_idx'],
				'dealings_status_idx'=>1
			];

			$insertProcessResult = $dealingsClass->insertDealingsProcess($processData);
			if ($insertProcessResult < 1) {
				throw new Exception('거래 처리과정 생성 실패! 관리자에게 문의하세요');
			}else {
				$db->commitTrans();
				
				$returnUrl = SITE_DOMAIN.'/voucher_dealings.php';
				$alertMessage = '판매가 취소되었습니다!';
			}
		} else if ($mode == 'payMileage') {
			//판매취소 
			$dealingsClass = new DealingsClass($db);
			$mileageClass = new MileageClass($db);
			$memberClass = new MemberClass($db);

			$_POST['dealings_idx'] = htmlspecialchars($_POST['dealings_idx']);
			$_POST['dealings_writer_idx'] = htmlspecialchars($_POST['dealings_writer_idx']);
			$_POST['dealings_status'] = htmlspecialchars($_POST['dealings_status']);
			$_POST['dealings_type'] = htmlspecialchars($_POST['dealings_type']);
			$_POST['purchaser_idx'] = htmlspecialchars($_POST['purchaser_idx']);
			$_POST['purchaser_mileage'] = htmlspecialchars($_POST['purchaser_mileage']);
			$_POST['dealings_mileage'] = htmlspecialchars($_POST['dealings_mileage']);
			$postData = $_POST;
			
			$returnUrl = SITE_DOMAIN.'/voucher_dealings.php';
			$purchaser_idx = $postData['purchaser_idx'];
			$purchaser_mileage = $postData['purchaser_mileage'];

			$db->beginTrans(); // 트랜잭션시작

			// 거래금액 확인
			$totalMileage = $memberClass->getTotalMileage($purchaser_idx);
			if ($totalMileage === false) {
				throw new Exception('거래 상태 오류! 관리자에게 문의하세요.');
			} else {
				if ($purchaser_mileage > $totalMileage) {
					throw new Exception('거래금액이 부족합니다! 충전하세요');
				}
			}

			$param = [
				'member_idx'=>$purchaser_idx,
				'charge_status'=>3
			];
			
			// 거래가능한 마일리지 리스트 가져오기
			$mileageWitdrawaLlist = $mileageClass->getMileageWithdrawalPossibleList($param);
			if ($mileageWitdrawaLlist === false) {
				throw new Exception('충전 내역을 가져오는 중에 오류가 발생하였습니다.');
			}

			//imi_mileage_charge 수량 변경을 위한 정보 얻어오기
            $dealings_mileage = $postData['dealings_mileage']; 
            $chargeArray = $mileageClass->getMildateChargeInfomationData($mileageWitdrawaLlist, $dealings_mileage);
            if ($chargeArray === false) {
                throw new Exception('수량정보 데이터를 가져오는 중에 오류가 발생하였습니다.');
            }
			$chargeData = $chargeArray['update_data'];
			$typeChargeData = $chargeArray['mileage_type_data'];

			$updateChargeResult = $mileageClass->updateMileageCharge($chargeData);
            if ($updateChargeResult === false) {
                throw new Exception('출금 수정 실패! 관리자에게 문의하세요');
            }

            // 트랜잭션 테스트 시 아래 제거 후 catch문에서 잘잡는지 확인
            $spareZeroCount = $mileageClass->getCountChargeSpareCountZero();
            if ($spareZeroCount < 0) {
                throw new Exception('마일리지 상태 조회 오류! 관리자에게 문의하세요');
            }

            if ($spareZeroCount > 0) {
                $updateZeroResult = $mileageClass->updateChargeZeroStatus();
                if ($updateZeroResult === false) {
                    throw new Exception('마일리지 출금 상태 변경 실패! 관리자에게 문의하세요');
                }
            }

			// 다음 거래상태 구하기
			$statusData = [
				'dealings_status'=>$postData['dealings_status']
			];

			$nextStatus = $dealingsClass->getNextDealingsStatus($statusData);
			if ($nextStatus === false) {
				throw new Exception('거래 상태 오류! 관리자에게 문의하세요.');
			}

			$mileageChangeParam = [
				'dealings_idx'=>$postData['dealings_idx'],
				'dealings_writer_idx'=>$postData['dealings_writer_idx'],
				'dealings_member_idx'=>$postData['purchaser_idx'],
				'dealings_status_code'=>$nextStatus
			];

			$changeData = $mileageClass->updateDealingsMileageArray($chargeData, $mileageChangeParam);
			if ($changeData === false) {
				throw new Exception('배열 데이터 생성 오류');
			}

			// 출금데이터 생성
            $insertChangeResult = $mileageClass->insertDealingsMileageChange($changeData);
            if ($insertChangeResult < 1) {
                throw new Exception('출금데이터 생성 실패! 관리자에게 문의하세요');
            }

			$mileageParam = [
                'mileage'=>$postData['dealings_mileage'],
                'idx'=>$purchaser_idx
            ];

            $updateResult = $memberClass->updateMileageWithdrawal($mileageParam); // 마일리지변경
            if ($updateResult < 1) {
                throw new Exception('회원 마일리지 수정 실패! 관리자에게 문의하세요');
            }

            $mileageTypeParam = [
				'idx'=>$purchaser_idx,
			];
            $mileageTypeUpdate = $mileageClass->mileageAllTypeWithdrawalUpdate($purchaser_idx, $typeChargeData);
            if ($mileageTypeUpdate < 1) {
                throw new Exception('마일리지 유형별 출금금액 수정 오류! 관리자에게 문의하세요!');
			}

			// 거래 데이터 상태 수정
			$dealingsParam = [
				'dealings_status'=>$nextStatus,
				'idx'=>$postData['dealings_idx']
			];

			$updateDealingsStatusResult = $dealingsClass->updateDealingsStatus($dealingsParam);
			if ($updateDealingsStatusResult < 1) {
				throw new Exception('거래 데이터 테이블 오류 발생! 관리자에게 문의하세요');
			}

			// 거래유저정보 상태 수정
			$userData = [
				'dealings_status'=>$nextStatus,
				'dealings_idx'=>$postData['dealings_idx']
			];

			$updateDealingsUserResult = $dealingsClass->updateDealingsUser($userData);
			if ($updateDealingsUserResult < 1) {
				throw new Exception('거래 유저 수정 오류! 관리자에게 문의하세요');
			}

			// 처리절차 생성하기
			$processData = [
				'dealings_idx'=>$postData['dealings_idx'],
				'dealings_status_idx'=>$nextStatus
			];

			$insertProcessResult = $dealingsClass->insertDealingsProcess($processData);
			if ($insertProcessResult < 1) {
				throw new Exception('거래 처리과정 생성 실패! 관리자에게 문의하세요');
			}else {
				$db->commitTrans();
				$alertMessage = '결제가 완료되었습니다.';
			}
		} else if ($mode == 'finish_dealings') {
			// return시 url 설정
			$returnUrl = SITE_ADMIN_DOMAIN.'/dealings_manage.php';

			$dealingsClass = new DealingsClass($db);
			$mileageClass = new MileageClass($db);
			$memberClass = new MemberClass($db);

			// xss, injection 방지
			$_GET['idx'] = htmlspecialchars($_GET['idx']);
			$_GET['member_idx'] = htmlspecialchars($_GET['member_idx']);
			$getData = $_GET;

			$dealingsIdx = $getData['idx'];
			$mileageType = 7; // 마일리지타입은 '거래'
			$chargeStatus = 3; // 충전상태는 '충전' 

			// 환불 및 완료 시 필요한 데이터 추출 
			$chargeData = $dealingsClass->getMileageChargeDataByDealings($dealingsIdx);
			if ($chargeData === false) {
				throw new Exception('충전데이터를 가져올 수 없습니다. 관리자에게 문의하세요');
			}
			$dealings_mileage = $chargeData->fields['dealings_mileage']; // 거래금액
			$commission = $chargeData->fields['commission']; // 수수료

			$chargeParam = [
				'idx'=>$getData['member_idx'],
				'account_bank'=>'아이엠아이',
				'account_no'=>setEncrypt($chargeData->fields['dealings_subject']),
				'charge_cost'=>$dealings_mileage-$commission,
				'spare_cost'=>$dealings_mileage-$commission,
				'charge_name'=>'관리자',
				'mileageType'=>$mileageType,
				'dealings_date'=>date('Y-m-d'),
				'charge_status'=>$chargeStatus
			];

			print_r2($chargeParam);

			
			/*
                if (!empty($expirationDate)) {
                    $chargeParam['expirationDate'] = $expirationDate;
                }

                $insertChargeResult = $mileageClass->insertMileageCharge($chargeParam); // 충전정보 추가
                if ($insertChargeResult < 1) {
                    throw new Exception('마일리지 충전 중 오류 발생! 관리자에게 문의하세요.');
                }

                $mileageParam = [
                    'charge_cost'=>$postData['charge_cost'],
                    'idx'=>$idx
                ];

                $updateResult = $memberClass->updateMileageCharge($mileageParam); // 마일리지변경
                if ($updateResult < 1) {
                    throw new Exception('마일리지 충전 변경 중에 오류 발생! 관리자에게 문의하세요!');
                }

                $memberMileageType = $mileageClass->getMemberMileageTypeIdx($idx);

                if ($memberMileageType == false) {
                    $mileageTypeParam = [
                        $idx, 
                        $postData['charge_cost']
                    ];
                    $mileageTypeInsert = $mileageClass->mileageTypeInsert($mileageType, $mileageTypeParam);
                    if ($mileageTypeInsert < 1) {
                        throw new Exception('마일리지 유형별 합계 삽입 중 오류 발생! 관리자에게 문의하세요!');
                    }
                } else {
                    $mileageTypeParam = [
                        $postData['charge_cost'],
                        $idx
                    ];
                    $mileageTypeUpdate = $mileageClass->mileageTypeChargeUpdate($mileageType, $mileageTypeParam);
                    if ($mileageTypeUpdate < 1) {
                        throw new Exception('마일리지 유형별 합계 변동 중 오류 발생 관리자에게 문의하세요!');
                    }
                }

				// 거래타입도 같이할것
				// 수수료도 넣기

			*/

			exit;
		}
	} catch (Exception $e) {
		if  ($connection === true) {
            $db->rollbackTrans();
			$alertMessage = $e->getMessage();
        }
	} finally {
		if  ($connection === true) {
            $db->completeTrans();
            $db->close();
        }

        if (!empty($alertMessage)) {
            alertMsg($returnUrl, 1, $alertMessage);
        } else {
            alertMsg($returnUrl, 0);
        }
	}