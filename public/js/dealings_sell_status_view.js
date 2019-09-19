$(function(){
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

		if (dealingsMileage > purchaserMileage){
			alert("마일리지가 부족합니다. 충전하세요!");
			return false;
		}

		$("#dealinges-sell-status-form").submit();
	});
});