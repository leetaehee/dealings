<?php
	/**
	 * 출금내역
	 */
	
	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_check.php';
    
	// adodb
    include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

	try {
		$title = TITLE_MY_WITHDRAWAL_LIST . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN . '/mypage.php';

        $alertMessage = '';

        $memberIdx = $_SESSION['idx'];

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }
		
		// 마일리지 출금 내역 조회 (출금, 환불, 유효기간 초과)
		$rMileageChangeQ = 'SELECT `idx`,
                                   `charge_cost`,
                                   `process_date`,
                                   `charge_account_no`,
                                   `charge_infomation`,
                                   `charge_name`,
                                   `charge_status`,
                                   `mileage_idx`
		                    FROM `th_mileage_change`
		                    WHERE `member_idx` = ?
		                    AND `charge_status` IN (2,4,5)';

		$rMileageChangeResult = $db->execute($rMileageChangeQ, $memberIdx);
		if ($rMileageChangeResult === false) {
		    throw new Exception('마일리지 출금 내역을 조회하면서 오류가 발생했습니다.');
        }

		$myWithdrawalData = [];

		foreach ($rMileageChangeResult as $key => $value) {
		    $changeIdx = $value['idx'];
		    $chargeCost = $value['charge_cost'];
            $processDate = $value['process_date'];
            $chargeAccountNo = $value['charge_account_no'];
            $chargeInfomation = $value['charge_infomation'];
            $chargeName = $value['charge_name'];
            $chargeStatus = $value['charge_status'];
            $mileageIdx = $value['mileage_idx'];

            // 마일리지 상태 조회
            $rMileageCodeQ = 'SELECT `mileage_name`
                              FROM `th_mileage_code`
                              WHERE `mileage_code` = ?';

            $rMileageCodeResult = $db->execute($rMileageCodeQ, $chargeStatus);
            if ($rMileageCodeResult === false) {
                throw new Exception('마일리지 상태를 조회하면서 오류가 발생했습니다.');
            }

            $mileageCodeName = $rMileageCodeResult->fields['mileage_name'];

            // 마일리지 종류 조회
            $rMileageQ = 'SELECT `charge_taget_name`
                          FROM `th_mileage`
                          WHERE `idx` = ?';

            $rMileageResult = $db->execute($rMileageQ, $mileageIdx);
            if ($rMileageResult === false) {
                throw new Exception('마일리지 종류를 조회하면서 오류가 발생했습니다.');
            }

            $chargeTagetName = $rMileageResult->fields['charge_taget_name'];

            $myWithdrawalData[] = [
                'seq'=> $key+1,
                'change_idx'=> $changeIdx,
                'charge_cost'=> $chargeCost,
                'process_date'=> $processDate,
                'charge_account_no'=> $chargeAccountNo,
                'charge_infomation'=> $chargeInfomation,
                'charge_name'=> $chargeName,
                'charge_status'=> $chargeStatus,
                'charge_taget_name'=> $chargeTagetName,
                'mileage_code_name'=> $mileageCodeName
            ];
        }

		$myWithdrawalDataCount = count($myWithdrawalData);

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/my_withdrawal_list.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if ($connection === true) {
			$db->close();
		}

		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	}

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout_main.html.php'; // 전체 레이아웃