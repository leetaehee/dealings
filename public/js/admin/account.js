$(function(){
	$("#submit-btn").on("click",function(){
		if (isAccountValidForm()==true) {
			$("#my-account-form").submit();
		}
	});
});

function isAccountValidForm()
{
	/*
	 * 출금 계좌 유효성 검사
	 */

	var accountBank = $("#accountBank").val();
	var accountNo = $("#accountNo").val();

	if (accountBank.length < 1) {
	   alert('은행명을 입력하세요.');
	   return false;
	} else {
		if (accountBank.length > 15) {
			alert('은행명은 15자이내로 입력하세요.');
			return false;
		}
	}
	
	if (accountNo.length < 1) {
	   alert("계좌번호를 입력하세요.");
	   return false;
	} else {
		if (accountNo.length > 18) {
			alert("계좌번호는 18자이내로 입력하세요");
			return false;
		}

		if(checkInteger(accountNo)==false){
			alert("계좌번호에는 숫자만 넣을 수 있습니다.(하이픈 제외)");
			return false
		}
	}
	return true;
}