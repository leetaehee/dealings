<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 
		1. 관리자 회원가입 처리 기능- 회원추가/회원수정/회원삭제 
		2. 모드- 회원가입: 'add', 회원수정: 'modi', 회원탈퇴: 'del'
		3. 회원 가입 성공 후, 세션에 다지 않고 로그인으로 가서 로그인하도록 할 것.
		4. 회원 탈퇴는 관리자는 할 수 없으며, 슈퍼관리자에게 권한해제를 요청해야 함.
	 */

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; // 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; // 공통함수
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/mailer.lib.php'; // PHP메일보내기

	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

    // Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/AdminClass.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/LoginClass.php';

	// Exception 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../Exception/RollbackException.php';

    try {
		// 리턴되는 화면 URL 초기화.
        $returnUrl = SITE_ADMIN_DOMAIN;
        $alertMessage = '';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

        if (isset($_POST['mode'])) {
            $mode = htmlspecialchars($_POST['mode']);
        } else {
            $mode = htmlspecialchars($_GET['mode']);
        }
        
        if ($mode == 'add') {
            $adminClass = new AdminClass($db);

            // 폼 데이터 받아서 유효성 검증
            $returnUrl = SITE_ADMIN_DOMAIN.'/join.php'; // 유효성검증 실패시, 리턴 UTL
            
            // injection, xss 방지코드
            $_POST['isOverlapEmail'] = htmlspecialchars($_POST['isOverlapEmail']);
            $_POST['isOverlapPhone'] = htmlspecialchars($_POST['isOverlapPhone']);
            $_POST['id'] = htmlspecialchars($_POST['id']);
            $_POST['password'] = htmlspecialchars($_POST['password']);
            $_POST['repassword'] = htmlspecialchars($_POST['repassword']);
            $_POST['name'] = htmlspecialchars($_POST['name']);
            $_POST['email'] = htmlspecialchars($_POST['email']);
            $_POST['phone'] = htmlspecialchars($_POST['phone']);
            $_POST['birth'] = htmlspecialchars($_POST['birth']);
            $_POST['sex'] = htmlspecialchars($_POST['sex']);        
            $postData = $_POST;
            
            $resultMemberValidCheck = $adminClass->checkAdminFormValidate($postData);
            if ($resultMemberValidCheck['isValid'] === false) {
                throw new Exception($resultMemberValidCheck['errorMessage']);
            } else {
                // 트랜잭션시작
                $db->beginTrans();
                
                $accountData = [
                    'phone'=>setEncrypt($postData['phone']),
                    'email'=>setEncrypt($postData['email']),
                    'id'=>$postData['id']
                ];
                
                $accountOverlapCount = $adminClass->getAccountOverlapCount($accountData);                
                if ($accountOverlapCount === false) {
                    throw new RollbackException('계정중복확인 오류 발생! 관리자에게 문의하세요');
                } else {
                    if ($accountOverlapCount > 0) {
                        throw new RollbackException('아이디/이메일/핸드폰 번호는 중복 될 수 없습니다.');
                    }
                }
            }

            if ($resultMemberValidCheck['isValid'] == true) {
                $insertResult = $adminClass->insertMember($postData);

                if ($insertResult > 0) {
                    $returnUrl = SITE_ADMIN_DOMAIN.'/join_complete.php'; // 회원가입 화면 URL 지정(가입완료화면)

                    $_SESSION['tmp_idx'] = 't_'.$idx; // 임시세션

                    $approvalUrl = SITE_ADMIN_DOMAIN.'/join_approval.php?idx='.$insertResult; // 승인링크 추가
                    $content = '<a href='.$approvalUrl.'>메일승인하러가기</a>';

                    //메일발송
                    mailer(MAIL_SENDER, MAIL_ADDRESS, $postData['email'], MAIL_TITLE, $content);
                    
                    $db->commitTrans();
					$alertMessage ='관리자 회원가입이 완료되었습니다! 이메일을 확인하세요!';
                } else {
                    throw new RollbackException('관리자 회원가입이 실패하였습니다!');
                }
            }
        } else if ($mode == 'modi') {
            // 회원정보수정
            $adminClass = new AdminClass($db);
            $isNewData = false;
			// 유효성검증 실패시, 리턴 UTL
			$returnUrl = SITE_ADMIN_DOMAIN.'/admin_modify.php?idx='.$postData['idx'];

            // injection, xss 방지코드
            $_POST['isOverlapEmail'] = htmlspecialchars($_POST['isOverlapEmail']);
            $_POST['isOverlapPhone'] = htmlspecialchars($_POST['isOverlapPhone']);
            $_POST['idx'] = htmlspecialchars($_POST['idx']);
            $_POST['password'] = htmlspecialchars($_POST['password']);
            $_POST['repassword'] = htmlspecialchars($_POST['repassword']);
            $_POST['name'] = htmlspecialchars($_POST['name']);
            $_POST['email'] = htmlspecialchars($_POST['email']);
            $_POST['phone'] = htmlspecialchars($_POST['phone']);
            $_POST['birth'] = htmlspecialchars($_POST['birth']);
            $_POST['sex'] = htmlspecialchars($_POST['sex']);
            $postData = $_POST;

            $resultAdminValidCheck = $adminClass->checkAdminFormValidate($postData);
            if ($resultAdminValidCheck['isValid'] == false) {
                throw new Exception($resultAdminValidCheck['errorMessage']);
            } else {
                // 트랜잭션시작
                $db->beginTrans();
                
                $accountData = [
                    'phone'=>setEncrypt($postData['phone']),
                    'email'=>setEncrypt($postData['email'])
                ];
                
                if ($postData['isOverlapEmail'] > 0) {
                    $isNewData = true; // 이메일 주소를 변경하는 경우
                }
                
                if ($postData['isOverlapPhone'] > 0) {
                    $isNewData = true;  // 핸드폰번호를 변경하는 경우
                }
                
                if ($isNewData === true) {
                    $accountOverlapCount = $adminClass->getAccountOverlapCount($accountData);
                    
                    if ($accountOverlapCount === false) {
                        throw new RollbackException('계정중복확인 오류 발생! 관리자에게 문의하세요');
                    } else {
                        if ($accountOverlapCount > 0) {
                            throw new RollbackException('이메일/핸드폰 번호는 중복 될 수 없습니다.');
                        }
                    }
                }
            }

            if ($resultAdminValidCheck['isValid'] == true) {
                // 트랜잭션시작
                $db->beginTrans();

                $updateResult = $adminClass->updateAdmin($postData);
                if ($updateResult > 0) {
                    $returnUrl = SITE_ADMIN_DOMAIN.'/admin_page.php'; // 수정 성공 시 마이페이지로 이동
                }else{
                    throw new RollbackException('회원정보 수정이 실패하였습니다! 관리자에게 문의하세요.');
                }

                $db->commitTrans();
				$alertMessage = '회원정보가 수정 되었습니다!';
            }
        } else if ($mode == 'del') {
            // 회원탈퇴
            $adminClass = new AdminClass($db);
            $loginClass = new LoginClass($db);

            // injection, xss 방지코드
            if (isset($_GET['idx'])) {
                $idx = htmlspecialchars($_GET['idx']);
            } else {
                $idx = htmlspecialchars($_POST['idx']);
            }

            if (isset($_GET['password'])) {
                $password = htmlspecialchars($_GET['password']);
            } else {
                $password = htmlspecialchars($_POST['password']);
            }

            $returnUrl = SITE_ADMIN_DOMAIN.'/admin_delete.php?idx='.$idx; // 유효성검증 실패시, 리턴 

            $param = [$idx, $password];

            // 트랜잭션시작
            $db->beginTrans();

            $checkLoginData = $loginClass->checkPasswordByAdmin($param);
            if ($checkLoginData === false) {
                throw new RollbackException('패스워드를 가져오다가 오류가 발생하였습니다.');
            }else{
                 if ($checkLoginData === null) {
                    throw new RollbackException('패스워드를 확인하세요!');
                }
                $returnUrl = SITE_ADMIN_DOMAIN;
            }

            $updateResult = $adminClass->deleteAdmin($idx);
            if ($updateResult < 1) {
                throw new RollbackException('회원 탈퇴 오류가 발생했습니다.');
            } else {
				$db->commitTrans();

				session_destroy();
				$alertMessage = '정상적으로 탈퇴되었습니다. 이용해주셔서 감사합니다.';
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