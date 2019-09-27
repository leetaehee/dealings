$(function(){
	$("#charge-btn").on("click",function(){
		if (isVirtualChargeValidForm()==true) {
			$("#virtual-charge-form").submit();
		}
	});

	$("#withdrawal-btn").on("click",function(){
		if (isVirtualWithdrawalValidForm()==true){
			$("#vitual-withdrawal-form").submit();
		}
	});

	$("#issue-btn").on("click",function() {
		if ($("#accountBank").val().length  < 1) {
			alert("은행을 선택하세요.");
			$("#accountNo").html("은행을 선택하세요");
			$(".accountNo").val("");

			return false;
		}
		getVirtualAccount($(this));
	});
});

function getVirtualAccount($this)
{
	/*
	 * 가상계좌번호 발급 및 조회 ajaxUrl  
	 */
	$.ajax({
		type: "post",
		url: ajaxUrl,
		data: {
			accountBank: $("#accountBank").val()
		},
		dataType: "json",
		success: function(data, status, xhr) {
			if(data.isSuccess==true){
			   $("#accountNo").html(data.account_no);
			   $(".accountNo").val(data.account_no);
			}else{
				alert(data.errorMessage);

				$("#accountNo").html('은행을 선택하세요.');
				$(".accountNo").val('');
			}
		},
		error: function(jdXHR, textStatus, errorThrown) {
		}
	});
}

function isVirtualChargeValidForm()
{
	/*
	 * 가상계좌 충전 시 시 유효성 검사 
	 * 1. 공백 시 유효성 처리  
	 * 2. 입금금액은 1000원 이상 결제 시에만 동작 
	 */

	var chargeCost = $("#chargeCost").val();

	if (chargeCost.length  < 1) {
		alert("입금금액을 입력하세요.");
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

	return true;
}

function isVirtualWithdrawalValidForm()
{
	/*
	 * 가상계좌 출금 시 시 유효성 검사 
	 * 1. 공백 시 유효성 처리  
	 * 2. 출금금액은 1000원 이상 결제 시에만 동작 
	 */
	
	var chargeCost = $("#chargeCost").val();
	var maxMileage = $("#maxMileage").val()

	if (chargeCost.length  < 1) {
		alert("출금 금액을 입력하세요.");
		return false;
	}else{
		
		if (checkInteger(chargeCost)==false) {
			alert("숫자만 입력 할 수 있습니다.");
			return false;
		}

		if (Number(chargeCost) < 1000) {
			alert("금액은 1000원 이상 출금 할 수 있습니다.");
			return false;
		}

		if (Number(chargeCost) > Number(maxMileage))
		{
			alert("출금 금액은 출금가능한 마일리지를 초과 할 수 없습니다");
			return false;
		}
	}

	return true;
}