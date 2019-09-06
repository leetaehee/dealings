$(function(){
	$("#charge-btn").on("click",function(){
		if (isPhoneChargeValidForm()==true) {
			$("#phone-charge-form").submit();
		}
	});
});

function isPhoneChargeValidForm()
{
	/*
	 * 휴대전화 충전 시 시 유효성 검사 
	 * 1. 공백 시 유효성 처리  
	 * 2. 입금금액은 1000원 이상 결제 시에만 동작 
	 */

	var accountBank = $("#accountBank").val();
	var chargeCost = $("#chargeCost").val();
	var chargeName = $("#chargeName").val();
	var accountNo = $("#accountNo").val();

	if (accountBank.length  < 1) {
		alert("통신사를 선택하세요.");
		return false;
	}

	if (accountNo.length < 1) {
		alert("휴대전화를 입력하세요");
		return false;
	} else {
		if(checkPhoneFormat(accountNo)==false){
			alert("1. 휴대전화에는 하이픈없이 숫자만 입력 가능합니다. \n2. 휴대전화 포맷을 확인하세요.");
			return false;
		}
	}

	if (chargeCost.length  < 1) {
		alert("충전금액을 입력하세요.");
		return false;
	}else{
		if (Number(chargeCost) < 1000) {
			alert("금액은 1000원 이상 충전 해야 합니다.");
			return false;
		}
	}

	if (chargeName.length < 1) {
		alert("입금자를 입력하세요");
		return false;
	}

	return true;
}