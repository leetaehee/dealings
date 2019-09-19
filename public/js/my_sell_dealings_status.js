$(function(){
	$("#submit-btn").on("click",function(){
		var dealingsStatus = $("#dealings-status").val();
		
		if (Number(dealingsStatus) > 2) {
			alert('결재가 되면 판매 취소가 불가능합니다.');
			return false;
		} else if (Number(dealingsStatus) == 1) {
			alert('거래대기 일 때는 취소 할 수 없습니다.');
			return false;
		} else {
			$("#my-sell-dealings-status").submit();
		}
	});
});