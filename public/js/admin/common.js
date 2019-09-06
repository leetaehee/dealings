function checkEmailFormat(email)
{
	/*
	 * 이메일 정규식
	 */
	var emailCheck = /^[A-Za-z0-9_\.\-]+@[A-Za-z0-9\-]+\.[A-Za-z0-9\-]+/;
	return emailCheck.test(email);
}

function checkPhoneFormat(phone)
{
	/*
	 * 휴대폰 정규식
	 */
	var phoneCheck = /^\d{3}\d{3,4}\d{4}$/;
	return phoneCheck.test(phone);
}

function checkBirthFormat(birth)
{
	/*
	 * 생년월일 정규식
	 */
	var birthCheck = /^(19|20)\d{2}-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[0-1])$/;
	return birthCheck.test(birth);
}

function checkIdFormat(id)
{
	/*
	 * 아이디 정규식 (소문자/대문자, 숫자로만 구성)
	 */
	var idCheck = /^[a-z|A-Z|0-9|\*]+$/;
	return idCheck.test(id);   
}

function checkNameFormat(name)
{
    /*
	 * 이름 정규식 (한글만 가능)
	 */
    var nameCheck = /[ㄱ-ㅎ|ㅏ-ㅣ|가-힣]/;
    return nameCheck.test(name);
}

function changeText(id)
{
	/*
	 * 첫글자는 대문자로 치환
	 */
	return id.substring(0,1).toUpperCase() + id.substring(1,id.length);
}

function checkInteger(integer)
{
	/*
	 * 숫자만 체크 (예- 하이픈없는 계좌번호)
	 */

	var integerCheck = /^[0-9]+$/;
	return integerCheck.test(integer);
}