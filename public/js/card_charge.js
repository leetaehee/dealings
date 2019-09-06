$(function(){
	$("#charge-btn").on("click",function(){
		if (isCardChargeValidForm()==true) {
			$("#card-charge-form").submit();
		}
	});
});

function isCardChargeValidForm()
{
	/*
	 * 신용카드 충전 시 시 유효성 검사 
	 * 1. 공백 시 유효성 처리  
	 * 2. 입금금액은 1000원 이상 결제 시에만 동작 
	 */

	var accountBank = $("#accountBank").val();
	var chargeCost = $("#chargeCost").val();
	var chargeName = $("#chargeName").val();
	var accountNo = $("#accountNo").val();

	if (accountBank.length  < 1) {
		alert("카드종류를 선택하세요.");
		return false;
	}

	if (accountNo.length < 1) {
		alert("카드번호를 입력하세요");
		return false;
	} else {
		if(checkInteger(accountNo)==false){
			alert("카드 번호에는 숫자만 넣을 수 있습니다.");
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