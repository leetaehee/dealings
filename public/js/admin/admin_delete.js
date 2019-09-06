$(function(){
	$("#submit-btn").on("click",function(){
		if (isMemberDeleteValidForm()==true) {
			$("#delete-form").submit();
		}
	});
});

function isMemberDeleteValidForm()
{
	/*
	 * 로그인 시 유효성 검사- 아이디, 비밀번호 비어있는지 체크
	 */

	var userPassword = $("#userPassword").val();

	if (userPassword.length < 1) {
	   alert('패스워드를 입력하세요.');
	   return false;
	}

	return true;
}