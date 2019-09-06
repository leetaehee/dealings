$(function(){
	$("#charge-btn").on("click",function(){
		if (isVoucherChargeValidForm()==true) {
			$("#voucher-charge-form").submit();
		}
	});
});

function isVoucherChargeValidForm()
{
	/*
	 * 문화상품권 충전 시 시 유효성 검사 
	 * 1. 공백 시 유효성 처리  
	 * 2. 입금금액은 1000원 이상 결제 시에만 동작 
	 */

	var chargeCost = $("#chargeCost").val();
	var chargeName = $("#chargeName").val();
	var accountNo = $("#accountNo").val();


	if (accountNo.length < 1) {
		alert("상품권 번호를 입력하세요.");
		return false;
	} else {
		if(checkIdFormat(accountNo)==false){
			alert("상품권 번호에는 소문자/대문자/숫자만 들어갈 수 있습니다.");
			return false;
		}
	}

	if (chargeCost.length  < 1) {
		alert("충전금액을 입력하세요.");
		return false;
	}else{
		if (checkInteger(chargeCost)==false) {
			alert("숫자만 입력 할 수 있습니다.");
			return false;
		}

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