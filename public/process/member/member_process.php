<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 
		1. 회원가입 처리 기능- 회원추가/회원수정/회원삭제 
		2. 모드- 회원가입: 'add', 회원수정: 'modi', 회원탈퇴: 'del'
		3. 회원 가입 성공 후, 세션에 다지 않고 로그인으로 가서 로그인하도록 할 것.
		4. 회원 탈퇴는 관리자는 할 수 없으며, 슈퍼관리자에게 권한해제를 요청해야 함.
	 */

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; // 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; // 공통함수
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/mailer.lib.php'; // PHP메일보내기

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php'; // adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php'; // adodb

    // Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MemberClass.php';
    include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/LoginClass.php';

	// Exception 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../Exception/RollbackException.php';

    try {
        $returnUrl = SITE_DOMAIN; // 리턴되는 화면 URL 초기화.
        $alertMessage = ''; // 메세지
        
        if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

        if (isset($_POST['mode'])) {
            $mode = htmlspecialchars($_POST['mode']);
        } else {
            $mode = htmlspecialchars($_GET['mode']);
        }
        
        if ($mode == 'add') {
            $memberClass = new MemberClass($db);

            // 폼 데이터 받아서 유효성 검증
            $returnUrl = SITE_DOMAIN.'/join.php'; // 유효성검증 실패시, 리턴 UTL

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
            
            $resultMemberValidCheck = $memberClass->checkMemberFormValidate($postData);

            if ($resultMemberValidCheck['isValid'] == false) {
                throw new Exception($resultMemberValidCheck['errorMessage']);
            } else {
                // 트랜잭션시작
                $db->beginTrans();
                
                $accountData = [
                    'phone'=>setEncrypt($postData['phone']),
                    'email'=>setEncrypt($postData['email']),
                    'id'=>$postData['id']
                ];
                
                $accountOverlapCount = $memberClass->getAccountOverlapCount($accountData);                
                if ($accountOverlapCount === false) {
                    throw new RollbackException('계정정보를 가져오지 못했습니다.');
                } else {
                    if ($accountOverlapCount > 0) {
                        throw new RollbackException('아이디/이메일/핸드폰 번호는 중복 될 수 없습니다.');
                    }
                }
            }

            if ($resultMemberValidCheck['isValid'] === true) {
                $insertResult = $memberClass->insertMember($postData);

                if ($insertResult > 0) {
                    $returnUrl = SITE_DOMAIN.'/join_complete.php'; // 회원가입 화면 URL 지정(가입완료화면)

                    $_SESSION['tmp_idx'] = 't_'.$insertResult; // 임시세션

                    $approvalUrl = SITE_DOMAIN.'/join_approval.php?idx='.$insertResult; // 승인링크 추가
                    $content = '<a href='.$approvalUrl.'>메일승인하러가기</a>';

                    //메일발송
                    mailer(MAIL_SENDER, MAIL_ADDRESS, $postData['email'], MAIL_TITLE, $content);

                    $db->commitTrans();
					$alertMessage = '회원 가입이 되었습니다. 이메일을 확인하세요';
                } else {
                    throw new RollbackException('회원 가입 실패하였습니다.');
                }
            } 
        } else if ($mode == 'modi') {
            // 회원정보수정
            $memberClass = new MemberClass($db);
            $isNewData = false;

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

            $postData = $_POST; // 폼데이터

            $returnUrl = SITE_DOMAIN.'/member_modify.php?idx='.$postData['idx']; // 유효성검증 실패시, 리턴 UTL

            $resultMemberValidCheck = $memberClass->checkMemberFormValidate($postData);
                
            if ($resultMemberValidCheck['isValid'] === false) {
                throw new Exception($resultMemberValidCheck['errorMessage']);
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
                    $accountOverlapCount = $memberClass->getAccountOverlapCount($accountData);
                    
                    if ($accountOverlapCount === false) {
                        throw new RollbackException('계정중복확인 오류 발생! 관리자에게 문의하세요');
                    } else {
                        if ($accountOverlapCount > 0) {
                            throw new RollbackException('아이디/이메일/핸드폰 번호는 중복 될 수 없습니다.');
                        }
                    }
                }
            }

            if ($resultMemberValidCheck['isValid'] == true) {
                $updateResult = $memberClass->updateMember($postData);

                if ($updateResult > 0) {
					$returnUrl = SITE_DOMAIN.'/mypage.php'; // 수정 성공 시 마이페이지로 이동

					$db->commitTrans();
					$alertMessage = '회원정보가 수정 되었습니다.'; 
                } else {
                    throw new RollbackException('회원 정보 수정 실패하였습니다.');  
                }
            }
        } else if ($mode == 'del') {
            // 회원탈퇴
        
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
            
            // 유효성검증 실패시, 리턴
            $returnUrl = SITE_DOMAIN.'/member_delete.php?idx='.$idx; 

            $memberClass = new MemberClass($db);
            $loginClass = new LoginClass($db);

            $param = [
                $idx,
                $password
            ];

            // 트랜잭션시작
            $db->beginTrans();
            
            $checkLoginData = $loginClass->checkPasswordByUser($param);

            if ($checkLoginData === false) {
                throw new RollbackException('패스워드 체크 오류! 관리자에게 문의하세요.');
            }else{
                if ($checkLoginData === null) {
                    throw new RollbackException('패스워드를 확인하세요!');
                }

            }

            $updateResult = $memberClass->deleteMember($idx);
            if ($updateResult < 1) {
                throw new RollbackException('회원 탈퇴 오류! 관리자에게 문의하세요!');
            } else {
				$returnUrl = SITE_DOMAIN;
				
				$db->commitTrans();
				session_destroy();

				$alertMessage = '정상적으로 탈퇴되었습니다. 이용해주셔서 감사합니다.';
			}
        } else if ($mode == 'account') {
            $returnUrl = SITE_DOMAIN.'/my_account.php';
		
            // injection, xss 방지코드
            $_POST['idx'] = htmlspecialchars($_POST['idx']);
            $_POST['account_bank'] = htmlspecialchars($_POST['account_bank']);
            $_POST['account_no'] = htmlspecialchars($_POST['account_no']);
            $postData = $_POST;

            $memberClass = new MemberClass($db);

            $resultAccountValidCheck = $memberClass->checkAccountFormValidate($postData);

            if ($resultAccountValidCheck['isValid'] === false) {
				throw new Exception($resultAccountValidCheck['errorMessage']);
            } else {
                $returnUrl = SITE_DOMAIN.'/mypage.php'; // 마이페이지로 이동

                $param = [
                    'accountNo'=>setEncrypt($postData['account_no']),
                    'accountBank'=>$postData['account_bank'],
                    'idx'=>$postData['idx']
                ];

                // 트랜잭션 시작
                $db->beginTrans();

                $updateResult = $memberClass->updateMyAccount($param);
                if ($updateResult < 1) {
                    throw new RollbackException('계좌정보가 입력이 실패했습니다.');
                } else {
                    $db->commitTrans();

					$alertMessage = '계좌정보가 설정되었습니다!';
                }
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