<?php
	/**
	 * 마일리지 결제내역
	 */
	
	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_admin_check.php';
    
	// adodb
    include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

	try {
		$title = TITLE_ADMIN_CHARGE_STATUS . ' | ' . TITLE_ADMIN_SITE_NAME;
		$returnUrl = SITE_ADMIN_DOMAIN . '/admin_page.php';

		$alertMessage = '';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

        $adminIdx = $_SESSION['mIdx'];

        // 충전내역 조회
        $rMileageChargeP = [
          'is_expiration'=> 'N',
          'charge_status'=> 1
        ];

        $rMileageChargeQ = 'SELECT `idx`,
                                   `member_idx`,
                                   `charge_date`,
                                   `charge_cost`,
                                   `spare_cost`,
                                   `use_cost`,
                                   `mileage_idx`,
                                   `charge_infomation`,
                                   `charge_account_no`
                            FROM `th_mileage_charge`
                            WHERE `is_expiration` = ?
                            AND `charge_status` <> ?
                            ORDER BY `charge_date` DESC, `mileage_idx` ASC';

        $rMileageChargeResult = $db->execute($rMileageChargeQ, $rMileageChargeP);
        if ($rMileageChargeResult === false) {
            throw new Exception('충전내역을 조회하면서 오류가 발생했습니다.');
        }

        $chargeData = [];

        foreach ($rMileageChargeResult as $key => $value) {
            // 충전취소 링크
            $chargeCancelUrl = MILEAGE_PROCESS_ACTION . '/mileage_cancel.php';

            $chargeIdx = $rMileageChargeResult->fields['idx'];
            $chargerIdx = $rMileageChargeResult->fields['member_idx'];
            $chargeDate = $rMileageChargeResult->fields['charge_date'];
            $spareCost = $rMileageChargeResult->fields['spare_cost'];
            $chargeCost = $rMileageChargeResult->fields['charge_cost'];
            $useCost = $rMileageChargeResult->fields['use_cost'];
            $chargeInfomation = $rMileageChargeResult->fields['charge_infomation'];
            $chargeAccountNo = setDecrypt($rMileageChargeResult->fields['charge_account_no']);
            $mileageIdx = $rMileageChargeResult->fields['mileage_idx'];

            // 충전자 상세 정보 출력
            $rChargerQ = 'SELECT `name`,
                                 `phone`,
                                 `id`
                          FROM `th_members`
                          WHERE `idx` = ?';

            $rChargerResult = $db->execute($rChargerQ, $chargerIdx);
            if ($rChargerResult === false) {
                throw new Exception('충전자 정보를 조회하면서 오류가 발생했습니다.');
            }

            $chargerName = setDecrypt($rChargerResult->fields['name']);
            $chargerPhone = setDecrypt($rChargerResult->fields['phone']);
            $chargerId = $rChargerResult->fields['id'];

            // 마일리지 조회
            $rMileageQ = 'SELECT `idx` `charge_target_idx`,
                                 `charge_taget_name`
                          FROM `th_mileage`
                          WHERE `idx` = ?';

            $rMileageResult = $db->execute($rMileageQ, $mileageIdx);
            if ($rMileageResult === false) {
                throw new Exception('마일리지 정보를 조회하면서 오류가 발생했습니다.');
            }

            $chargeTargetIdx = $rMileageResult->fields['charge_target_idx'];
            $chargeTargetName = $rMileageResult->fields['charge_taget_name'];

            // 거래, 이벤트는 취소 버튼 비활성화
            $isCancelDisabled = false;
            if ($chargeTargetIdx != 7 && $chargeTargetIdx != 8) {
                $isCancelDisabled = true;
            }

            // 충전취소 링크
            $chargeCancelUrl = MILEAGE_PROCESS_ACTION . '/mileage_cancel.php';
            $chargeCancelUrl .= '?idx=' . $chargeIdx;

            // 충전내역 데이터 추가
            $chargeData[] = [
                'seq'=> $key+1,
                'name'=> $chargerName,
                'phone'=> $chargerPhone,
                'id'=> $chargerId,
                'is_cancel_disabled'=> $isCancelDisabled,
                'charge_target_name'=> $chargeTargetName,
                'charge_idx'=> $chargeIdx,
                'charger_idx'=> $chargerIdx,
                'charge_date'=> $chargeDate,
                'charge_cost'=> $chargeCost,
                'spare_cost'=> $spareCost,
                'use_cost'=> $useCost,
                'charge_infomation'=> $chargeInfomation,
                'charge_account_no'=> $chargeAccountNo,
                'charge_cancel_url'=> $chargeCancelUrl
            ];
        }

        $chargeDataCount = count($chargeData);

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/charge_list.html.php';
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
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/layout_main.html.php'; // 전체 레이아웃