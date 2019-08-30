<form id="join-form" method="post" action="<?=$actionUrl?>">
	<input type="hidden" name="mode" value="<?=$actionMode?>">
	<label for="user-id">아이디: </label><br>
	<input type="text" id="user-id" name="id" value=""><br>
	<label for="user-password">패스워드: </label><br>
	<input type="password" id="user-password" name="password" value=""><br>
	<label for="user-repassword">패스워드확인: </label><br>
	<input type="password" id="user-repassword" name="repassword" value=""><br>
	<label for="user-name">이름: </label><br>
	<input type="text" id="user-name" name="name" value=""><br>
	<label for="user-email">이메일: </label><br>
	<input type="text" id="user-email" name="email" value="">
	<small><?=JOIN_FORM_EMAIL_CAUTION_WRITE?></small><br>
	<label for="user-phone">휴대폰: </label><br>
	<input type="text" id="user-phone" name="phone" value="">
	<small><?=JOIN_FORM_PHONE_CAUTION_WRITE?></small><br>
	<label for="user-birth">생년월일: </label><br>
	<input type="text" id="user-birth" name="birth" value="">
	<small><?=JOIN_FORM_BIRTH_CAUTION_WRITE?></small><br>
	<label for="user-account-no">계좌번호: </label><br>
	<input type="text" id="user-account-no" name="account_no" value="">
	<small><?=JOIN_FORM_ACCOUNT_CAUTION_WRITE?></small><br>
	<label for="user-account-bank">계좌번호 은행: </label><br>
	<input type="text" id="user-account-bank" name="account_bank" value=""><br>
	<label for="user-sex">성별: </label><br>
	<input type="radio" id="user-sex" name="sex" value="M"> 남 
	<input type="radio" id="user-sex" name="sex" value="W"> 여 <br>
	<input type="submit" value="회원가입">
</form>