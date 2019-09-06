<p><h3>[출금계좌 설정]</h3></p>
<form id="my-account-form" action="<?=$actionUrl?>" method="post"> 
	<input type="hidden" name="mode" value="<?=$actionMode?>">
	<input type="hidden" id="oldAccountNo" value="<?=$account_no?>">
	<input type="hidden" id="oldAccountBank" value="<?=$account_bank?>">
	
	<p>(주의) 계좌번호나 은행명은 오타없이 적어주세요. 계좌번호는 하이픈을 제거해주세요.</p>
	<input type="hidden" name="idx" value="<?=$idx?>">
	<label for="accountBank">은행명: </label><br>
	<input type="text" id="accountBank" name="account_bank" value="<?=$account_bank?>"><br>
	<label for="accountNo">계좌번호: </label><br>
	<input type="text" id="accountNo" name="account_no" value="<?=$account_no?>"><br>
	<input type="button" id="submit-btn" value="적용">
</form>
