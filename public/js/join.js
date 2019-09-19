$(function(){
	$("#submit-btn").on("click",function(){
		if (isJoinValidForm()==true) {
			$("#join-form").submit();
		}
	});
	
	$("#userId, #userEmail, #userPhone").on("keyup",function(event){
		getOverlapAjax($(this));
	});
});

function getOverlapAjax($this)
{
	/*
	 * ajax로 중복확인 처리
	 */
	var mode = 'get' + changeText($this.prop('id'));

	var oldUserEmail = $("#oldUserEmail").val();
	var oldUserPhone = $("#oldUserPhone").val();
	var $id = $this;
	var isExcute = true;
	
	if($id.prop("id")=="userEmail"){
		if(oldUserEmail==$id.val()){
			isExcute = false;
		}  
	}
	
	if($id.prop("id")=="userPhone"){
		if(oldUserPhone==$id.val()){
			isExcute = false;
		}  
	}

	$.ajax({
		type: "post",
		url: ajaxUrl,
		data: {
			mode: "overlapCheck",
			detail_mode: mode,
			val: $this.val()
		},
		dataType: "json",
		success: function(data, status, xhr) {
			if (data.result==1) {
				switch(data.detail_mode){
					case "getUserId":
						$("#isOverlapId").val(data.result);
						$("#checkIdMessage").html("이미 가입되어 있는 ID입니다. 다시 입력하세요!");
						break;
					case "getUserEmail":
						if(isExcute==true){
							$("#isOverlapEmail").val(data.result);
							$("#checkEmailMessage").html("이미 가입되어 있는 EMAIL입니다. 다시 입력하세요!");
						}else{
							$("#isOverlapEmail").val(0);
							$("#checkEmailMessage").html("");
						}
						break;
					case "getUserPhone":
						if(isExcute==true){
							$("#isOverlapPhone").val(data.result);
							$("#checkPhoneMessage").html("이미 가입되어 있는 핸드폰입니다. 다시 입력하세요!");
						}else{
							$("#isOverlapPhone").val(0);
							$("#checkPhoneMessage").html("");
						}
						break;
				}
			} else {
				switch(data.detail_mode){
					case "getUserId":
						$("#isOverlapId").val(data.result);
						$("#checkIdMessage").html('가입되지 않은 ID입니다. 사용하셔도 좋습니다.!');
						break;
					case "getUserEmail":
						$("#isOverlapEmail").val(data.result);
						$("#checkEmailMessage").html("가입되지 않은 EMAIL 않았습니다. 사용하셔도 좋습니다.");
						break;
					case "getUserPhone":
						$("#isOverlapPhone").val(data.result);
						$("#checkPhoneMessage").html("가입되지 않은 핸드폰입니다.사용하셔도 좋습니다.");
						break;
				}
			}
		},
		error: function(jdXHR, textStatus, errorThrown) {
			// error.. call manager..
		}
	});
}

function isJoinValidForm()
{
	/*
	 * 회원가입 시 유효성 검사 
	 * 1. 아이디, 이름은 한글 또는 영문만 들어가는 함수 만들것  
	 * 2. 이메일, 핸드폰, 생년월일 형식 확인 
	 */
	var userId = $("#userId").val();
	var userName = $("#userName").val();
	var userPassword = $("#userPassword").val();
	var userRepassword = $("#userRepassword").val();
	var userEmail = $("#userEmail").val();
	var userPhone = $("#userPhone").val();
	var userBirth = $("#userBirth").val();

	if (userId.length > 0 ) {
	   if (userId.length > 20) {
		   alert("아이디는 최대 20자까지 허용합니다.");
		   return false;
		}
		
		if (checkIdFormat(userId)==false) {
			alert("아이디는 소문자/대문자/숫자로만 사용할 수 있습니다.");
			return false;
		}
	} else {
	   alert('아이디를 입력하세요.');
	   return false;
	}

	if (userName.length > 0) {
        if (userName.length > 4) {
			alert('이름은 최대 4자까지 허용합니다.');
			return false;
		}

		if (userName.length < 2) {
			alert('이름은 최소 2자 이상 입력해야합니다.');
			return false;
        }
        
        if (checkNameFormat(userName)==false) {
			alert("이름은 한글만 입력 할 수 있습니다.");
			return false;
		}
        
	} else {
		alert("이름을 입력하세요.");
		return false;
	}
	
	if (userPassword.length > 0 && userRepassword.length > 0) {
		if (userPassword.length < 8) {
			alert("패스워드는 최소 8자 입력하세요.");
			return false;
		}
			
		if (userPassword != userRepassword) {
		   alert("패스워드가 서로 같지 않습니다.");
		   return false;
		}
	} else {
	   alert("패스워드 입력란에 모두 입력하세요.");
	   return false;
	}
	
	// 이메일, 폰, 생년월일 형식 체크 
	if (userEmail.length > 0) {
		if (userEmail.length > 30){
			alert("이메일은 30자이내로 입력하세요.");
			return false;
		} 
		
		if (checkEmailFormat(userEmail)==false) {
			alert("이메일 형식이 올바르지 않습니다.");
			return false;
		}
	} else {
		alert("이메일을 입력하세요");
		return false;
	}
	
	if (userPhone.length > 0) {
		var search = '-';
		var user_phone = userPhone;
		
		if (user_phone.indexOf(search) != -1) {
			alert("휴대폰 번호에서 하이픈을 제거 해주세요.");
			return false;
		}
			
		if (checkPhoneFormat(userPhone)==false) {
			alert("휴대폰 형식이 올바르지 않습니다.");
			return false;
		}
	} else {
		alert("휴대폰을 입력하세요");
		return false;
	}
	
	if (userBirth.length > 0) {
		if(userBirth.length > 10) {
		   alert("생년월일은 10자이내로 입력하세요.");
		   return false;   
		}
		
		if (checkBirthFormat(userBirth)==false) {
			alert("생년월일 형식이 올바르지 않습니다.");
			return false;
		}
	} else {
		alert("생년월일을 입력하세요.");
		return false;
	}

	if (Number($("#isOverlapId").val())==1) {
		alert('이미 사용중인 아이디입니다. 수정하세요!');
		return false;
	}

	if (Number($("#isOverlapEmail").val())==1) {
		alert('이미 사용중인 이메일입니다. 수정하세요!');
		return false;
	}

	if (Number($("#isOverlapPhone").val())==1) {
		alert('이미 사용중인 핸드폰입니다. 수정하세요!');
		return false;
	}

	return true;
}