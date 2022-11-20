<?php
	/**
	 * 마이페이지
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
		$title = TITLE_MY_CHARGE_LIST . ' | ' . TITLE_SITE_NAME;

		$returnUrl = SITE_DOMAIN.'/mypage.php';
		$alertMessage = '';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

        $memberIdx = $_SESSION['idx'];

        // 마일리지 충전내역 조회
        $rMileageChargeP = [
          'member_idx'=> $memberIdx,
          'is_expiration'=> 'N'
        ];
        $rMileageChargeQ = 'SELECT `mileage_idx`,
                                   `charge_cost`,
                                   `use_cost`,
                                   `charge_date`,
                                   `charge_infomation`,
                                   `charge_account_no`,
                                   `charge_name`,
                                   `charge_status`
							FROM `th_mileage_charge`
							WHERE `member_idx` = ?
					        AND `charge_status` IN (1,3)
					        AND `is_expiration` = ?';

        $rMileageChargeResult = $db->execute($rMileageChargeQ, $rMileageChargeP);
        if ($rMileageChargeResult === false) {
            throw new Exceptiomn('마일리지 충전내역을 조회하면서 오류가 발생했습니다.');
        }

        $chargeData = [];

        foreach ($rMileageChargeResult as $key => $value) {
            // 마일리지 고유정보
            $mileageIdx = $value['mileage_idx'];
            $chargeStatus = $value['charge_status'];

            // 마일리지 상태 조회
            $rMileageNameQ = 'SELECT `mileage_name`
                              FROM `th_mileage_code`
                              WHERE `mileage_code` = ?';

            $rMileageNameResult = $db->execute($rMileageNameQ, $chargeStatus);
            if ($rMileageNameResult === false) {
                throw new Exception('마일리지 상태를 조회하면서 오류가 발생했습니다.');
            }

            $mileageName = $rMileageNameResult->fields['mileage_name'];

            // 마일리지 종류 조회
            $rMileageKindQ = 'SELECT `charge_taget_name`
                              FROM `th_mileage`
                              WHERE `idx` = ?';

            $rMileageKindResult = $db->execute($rMileageKindQ, $mileageIdx);
            if ($rMileageKindResult === false) {
                throw new Exception('마일리지 종류를 조회하면서 오류가 발생했습니다.');
            }

            $chargeTagetName = $rMileageKindResult->fields['charge_taget_name'];

            // 화면에 보여지는 충전내역 데이터
            $chargeData[] = [
                'seq'=> $key+1,
                'charge_date'=> $value['charge_date'],
                'charge_infomation'=> $value['charge_infomation'],
                'charge_account_no'=> $value['charge_account_no'],
                'charge_taget_name'=> $chargeTagetName,
                'charge_cost'=> $value['charge_cost'],
                'use_cost'=> $value['use_cost'],
                'mileage_name'=> $mileageName,
                'charge_name'=> $value['charge_name']
            ];
        }

        $chargeCount = count($chargeData);

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/my_charge_list.html.php';
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