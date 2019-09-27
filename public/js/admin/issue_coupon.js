$(function(){
	$("#submit-btn").on("click",function(){
		if (isIssueCouponValidForm()==true) {
			$("#issue-coupon-form").submit();
		}
	});
});

function isIssueCouponValidForm()
{
	var couponSubject = $("#coupon-subject").val();
	var voucherName = $("#voucher-name").val();
	var voucherPrice = $("#voucher-price").val();
	var discountRate = $("#discount-rate").val();
	var couponIssueType = $("#coupon-issue-type").val();
	var startDate = $("#start-date").val();
	var expirationDate = $("#expiration-date").val();

	if (couponIssueType.length < 1) {
		alert("발행쿠폰타입을 선택하세요.");
		return false;
	}

	if (couponSubject.length < 1) {
		alert("발행쿠폰명칭을 입력하세요.");
		return false;
	} else {
		if (couponSubject.length > 100) {
			alert("발행쿠폰명칭은 100자이내로 입력하세요.");
			return false;
		}
	}

	if (voucherName.length < 1){
		alert("상품권을 선택하세요.");
		return false;
	}

	if (voucherPrice.length < 1){
		alert("상품권 가격을 선택하세요.");
		return false;
	}

	if (discountRate.length < 1){
		alert("할인율을 입력하세요");
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

	if (startDate.length < 1){
		alert("쿠폰 적용일자를 입력하세요");
		return flase;
	} else {
		if (checkBirthFormat(startDate)==false){
			alert("쿠폰 적용일자의 날짜 형식을 2009-11-11 와 같이 입력하세요.");
			return false;
		}

	}

	if (expirationDate.length < 1){
		alert("쿠폰 만료일자를 입력하세요");
		return false;
	} else {
		if (checkBirthFormat(expirationDate)==false){
			alert("쿠폰 만료일자의 날짜 형식을 2009-11-11 와 같이 입력하세요.");
			return false;
		}
	}
	
	return true;
}