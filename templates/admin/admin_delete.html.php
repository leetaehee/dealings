<form id="delete-form" method="post" action="<?=$actionUrl?>">
	<input type="hidden" name="mode" value="<?=$actionMode?>">
    <input type="hidden" name="idx" value="<?=$idx?>">
    
    <p><?=$_SESSION['mName']?>(<?=$_SESSION['mId']?>) 님!</p>
    <p>회원탈퇴를 하시게 되면 계정은 복구 할 수 없습니다.</p>
    <p>탈퇴를 원하실 경우 비밀번호 입력 후 회원탈퇴 버튼을 눌러주세요!</p>
    패스워드: <input type="password" id="userPassword" name="password" value="">
	<input type="button" id="submit-btn" value="회원탈퇴"><br>
</form>
