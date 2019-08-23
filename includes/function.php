<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 공용함수
	 */

	function check_memberForm_validate($postData,$pdo=null){
		/**
		 * @author: LeeTaeHee
		 * @param: 입력 폼 배열 데이터
		 * @brief: 폼 데이터 유효성 검증(로그인/회원가입).
		 * @return: boolean
		 */
		$is_valid = true;
		$repEmail = "/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i";

		foreach($postData as $key=>$value){
			if($key!='mode' && empty($postData[$key])){
				// 빈 값 있는지 체크
				$is_valid = false;
				break;
			}
			if($key=='re_password' && ($postData['password']!=$postData['re_password'])){
				// 패스워드가 서로 일치하는지 체크
				$is_valid = false;
				break;
			}
			if($key=='email' && preg_match($repEmail,$postData['email'])==false){
				// 이메일 형식 맞는지 체크
				$is_valid = false;
				break;
			}
			if($postData['mode']=='add'){
				// 회원가입시에는 db에 등록되어있는 정보가 있는지 파악해야 함
				$stmt = $pdo->prepare('SELECT count(id) cnt FROM `imi_members` WHERE `id` = :id');
				$stmt->bindValue(':id',$postData['id']);
				$stmt->execute();
				$idResult = $stmt->fetch();	

				if($idResult['cnt'] > 0){
					$is_valid = false; // id 중복체크
					break;
				}

				$stmt = $pdo->prepare('SELECT count(email) cnt FROM `imi_members` WHERE `email` = :email');
				$stmt->bindValue(':email',$postData['email']);
				$stmt->execute();
				$emailResult = $stmt->fetch();

				if($emailResult['cnt'] > 0){
					$is_valid = false; // email 중복체크
					break;
				}

				$stmt = $pdo->prepare('SELECT count(phone) cnt FROM `imi_members` WHERE `phone` = :phone');
				$stmt->bindValue(':phone',$postData['phone']);
				$stmt->execute();
				$phoneResult = $stmt->fetch();

				if($phoneResult['cnt'] > 0){
					$is_valid = false; // phone 중복체크
					break;
				}
			}
		}
		return $is_valid;
	}

	function getLowGrade($pdo){
		/**
		 * @author: LeeTaeHee
		 * @param: $pdo 객체
		 * @brief: 등급 테이블(imi_member_grade)에서 가장 낮은 등급을 가져온다..
		 * @return: 0- 실패, 1- 성공
		 */
		 $stmt = $pdo->prepare('SELECT MIN(grade_order) grade_code from `imi_member_grades`');
		 $stmt->execute();
		 $result = $stmt->fetch();

		 return $result['grade_code'];
	}