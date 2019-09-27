$(function(){
	$("#submit-btn").on("click",function(){
		if (isDisCountValidForm()==true) {
			$("#setting-discount-form").submit();
		}
	});
});

function isDisCountValidForm()
{
	var voucherName = $("#voucher-name").val();
	var voucherPrice = $("#voucher-price").val();
	var discountRate = $("#discount-rate").val();

	if (voucherName.length < 1){
		alert("상품권을 선택하세요.");
		return false;
	}

	if (voucherPrice.length < 1){
		alert("상품권 가격을 선택하세요.");
		return false;
	}

	if (discountRate.length < 1){
		alert("최대 할인율을 입력하세요");
		return false;
	} else {
		discountRate = Number(discountRate);

		if (discountRate < 1) {
			alert('할인율은 0 이상 입력하세요');
			return false;
		}

		if (discountRate > 100) {
			alert('할인율은 100% 범위내에서 입력하세요.');
			return false;
		}

		if (checkInteger(discountRate) == false) {
			alert('숫자만 입력하세요');
			return false;
		}
	}
	
	return true;
}