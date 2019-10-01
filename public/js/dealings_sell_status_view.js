$(function(){
	var isDiscount = false;
	var total = 0;
	var couponDiscount = false;

	$("#submit-btn").on("click",function(){
		var dealingsStatus = $("#dealings-status").val()*1;
		var dealingsMileage = $("#dealings-mileage").val()*1;
		var purchaserMileage = $("#purchaser-mileage").val()*1;
		
		if (dealingsStatus > 2) {
			alert("결재가 되면 판매 취소가 불가능합니다.");
			return false;
		} 
		
		if (dealingsStatus == 1) {
			alert("거래대기 일 때는 취소 할 수 없습니다.");
			return false;
		}

		if (isDiscount == true){			
			// 결제할 금액이 있는 경우
			if(total > purchaserMileage) {
				alert("이용 가능한 마일리지가 부족합니다. 충전하세요!");
				return false;
			}
		} else {
			if (couponDiscount == false) {
				if (dealingsMileage > purchaserMileage){
					alert("이용 가능한 마일리지가 부족합니다. 충전하세요!");
					return false;
				}
			}
		}

		$("#dealinges-sell-status-form").submit();
	});

	$("#coupon-name").on("change", function(){

		var finalPaymentSum = dealingsMileage;
		var discountRate = $(this).find("option:selected").data("discount_rate");

		couponDiscount = false;

		if (typeof discountRate != 'undefined') {
			total = (Number(finalPaymentSum)*Number(discountRate))/100;

			if(total < 1) {
				total = 0;
			}

			if (finalPaymentSum == total) {
				// 쿠폰으로 전액 할인
				total = 0;
				couponDiscount = true;
			} else {
				// 일부잔액으로 거래한 경우
				isDiscount = true;
			}
		} else {
			total = finalPaymentSum;
		}

		total = Math.floor(total);

		$("#finalPaymentSum").html(addComma(total));
	});
});