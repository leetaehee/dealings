<p><h3>[출금계좌 설정]</h3></p>
<form id="my-account-form" action="<?=$actionUrl?>" method="post"> 
	<input type="hidden" id="oldAccountNo" value="<?=$accountNo?>">
	<input type="hidden" id="oldAccountBank" value="<?=$accountBank?>">
	
	<p>
		(주의) 계좌번호나 은행명은 오타없이 적어주세요. 계좌번호는 하이픈을 제거해주세요.
	</p>

	<label for="accountBank">은행명: </label><br>
	<?php if(count($CONFIG_BANK_ARRAY) > 0):?>
		<select id="accountBank" name="account_bank">
			<option value="">선택하세요.</option>
			<?php for($i=0; $i<count($CONFIG_BANK_ARRAY); $i++): ?>
				<option value="<?=$CONFIG_BANK_ARRAY[$i]?>" <?php if($CONFIG_BANK_ARRAY[$i]==$accountBank){ echo 'selected'; }?>>
					<?=$CONFIG_BANK_ARRAY[$i];?>
				</option>
			<?php endfor; ?>
		</select>
	<?php else: ?>
		<p>관리자에게 문의하세요!</p>
	<?php endif; ?>
	<br>
	<label for="accountNo">계좌번호: </label><br>
	<input type="text" id="accountNo" name="account_no" value="<?=$accountNo?>"><br>
	<input type="button" id="submit-btn" value="적용">
</form>
