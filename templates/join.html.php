<form id="join-form" method="post" action="<?=$actionUrl?>">
	<input type="hidden" name="mode" value="<?=$actionMode?>">
    <input type="hidden" name="idx" value="<?=$idx?>">
	<input type="hidden" id="isOverlapId" value="0">
	<input type="hidden" name="isOverlapEmail" id="isOverlapEmail" value="0">
	<input type="hidden" name="isOverlapPhone" id="isOverlapPhone" value="0">
    
    <input type="hidden" id="oldUserEmail" value="<?=$userEmail?>">
    <input type="hidden" id="oldUserPhone" value="<?=$userPhone?>">
	
    <?php if(!empty($userId)): ?>
        <label for="userId">아이디: </label><br>
        <input type="hidden" id="userId" value="<?=$userId?>">
        <?=$userId?><br>
    <?php else: ?>
        <label for="userId">아이디: </label><br>
	    <input type="text" id="userId" name="id" value="">
        <span id="checkIdMessage"></span><br>
    <?php endif; ?>
	<label for="userPassword">패스워드: </label><br>
	<input type="password" id="userPassword" name="password" value=""><br>
	<label for="userRepassword">패스워드확인: </label><br>
	<input type="password" id="userRepassword" name="repassword" value=""><br>
	<label for="userName">이름: </label><br>
	<input type="text" id="userName" name="name" value="<?=$userName?>"><br>
	<label for="userEmail">이메일: </label>
	<small><?=JOIN_FORM_EMAIL_CAUTION_WRITE?></small><br>
	<input type="text" id="userEmail" name="email" value="<?=$userEmail?>">
	<span id="checkEmailMessage"></span><br>
	<label for="userPhone">휴대폰: </label>
	<small><?=JOIN_FORM_PHONE_CAUTION_WRITE?></small><br>
	<input type="text" id="userPhone" name="phone" value="<?=$userPhone?>">
	<span id="checkPhoneMessage"></span><br>
	<label for="userBirth">생년월일: </label>
	<small><?=JOIN_FORM_BIRTH_CAUTION_WRITE?></small><br>
	<input type="text" id="userBirth" name="birth" value="<?=$userBirth?>"><br>
	<label for="userSex">성별: </label><br>
	<input type="radio" id="userSex" name="sex" value="M" <?=$userSexMChecked?>> 남 
	<input type="radio" id="userSex" name="sex" value="W" <?=$userSexWChecked?>> 여 <br>
	
    <?php if(isset($idx)): ?>
        <input type="button" id="submit-btn" value="정보수정">
    <?php else: ?>
        <input type="button" id="submit-btn" value="회원가입">
    <?php endif; ?>
</form>
