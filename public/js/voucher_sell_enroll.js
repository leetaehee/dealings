$(function(){
	$("#sell-btn").on("click",function(){
		if (isVourcherPurchaseValidForm()==true) {
			$("#vourcher-sell-form").submit();
		}
	});

	$("#itemNo").on("change",function(){
		$("#display-commission").html("");
	});

	$("#dealingsMileage").on("keyup",function(event){
		var dealingsMileage = $(this).val();
		var commission = $("#itemNo > option:selected").prop('class');
		var commissionStr = '';

		var result = getCommission(dealingsMileage, commission);

		if(result > 0)
		{
			commissionStr = "(수수료: "+commission+"% / 실수령액: "+result+"원)";
		}
		$("#display-commission").html(commissionStr);
	});

	$("#itemNo, #itemMoney").on("change", function(){
		$("#vourcher-sell-form").attr('action',domain+'/voucher_sell_enroll.php');
		$("#vourcher-sell-form").submit();	
	});
});

function isVourcherPurchaseValidForm(){
	/*
	 * 상품권 구매 시 유효성 검사 
	 * 1. 제목, 내용, 비고에 빈값,글자수 제한 체크  (글자수는 필수아님)
	 * 2. 거래금액은 최대 20만원까지 체크
	 * 3. 상품권 고유번호는 하이픈 제외하고 영문자 숫자만 허용 
	 */

	var dealingsSubject = $("#dealingsSubject").val();
	var dealingsContent = $("#dealingsContent").val();
	var itemNo = $("#itemNo").val();
	var itemMoney = $("#itemMoney").val();
	var itemObjectNo = $("#itemObjectNo").val();
	var dealingsMileage = Number($("#dealingsMileage").val());
	var memo = $("#memo").val();
	
	if (dealingsSubject.length > 0) {
		if (dealingsSubject.length < 10) {
			alert('제목은 최소 10자 이내로 입력하세요');
			return false;
		}

		if (dealingsSubject.length > 80) {
			alert('제목은 80자 이내로 입력하세요');
			return false;
		}
	} else {
		alert('제목을 입력하세요');
		return false;
	}

	if (dealingsContent.length > 0) {
		if (dealingsContent.length < 10) {
			alert('내용은 최소 10자 이내로 입력하세요');
			return false;
		}

		if (dealingsContent.length > 500) {
			alert('내용은 500자 이내로 입력하세요');
			return false;
		}
	} else {
		alert('내용을 입력하세요');
		return false;
	}

	if (itemNo.length < 1) {
		alert('상품권을 선택하세요.');
		return false;
	}

	if (itemMoney.length < 1) {
		alert('상품권 가격을 선택하세요.');
		return false;
	}

	if (itemObjectNo.length > 0) {
		if (checkIdFormat(itemObjectNo) == false) {
			alert('상품권 고유번호에는 소문자/대문자/숫자만 입력 할 수 있습니다.');
			return false;
		}

		if (itemObjectNo.length > 16) {
			alert('상품권 고유번호는 16자이내로 입력 할 수 있습니다.');
			return false;
		}
	} else {
		alert('상품권 고유번호를 입력하세요');
		return false;
	}


	if (dealingsMileage != 'undefined') {
		if (dealingsMileage == '') {
			alert('거래금액을 입력하세요.');
			return false;
		} else {
			if (dealingsMileage < 1000) {
				alert('거래금액은 1000원 이상 입력하세요.');
				return false;
			}

			if (dealingsMileage > 200000) {
				alert('거래금액은 200,000원을 초과 할 수 없습니다.');
				return false;
			}
		}

		if (memo.length > 100) {
			alert('메모는 100자이내로 입력하세요.');
			return false;
		}
	}

	return true;
}