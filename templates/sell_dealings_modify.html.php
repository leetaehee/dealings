<form id="vourcher-sell-form" method="post" action="<?=$actionUrl?>">
	<input type="hidden" name="mode" value="<?=$actionMode?>">
	<input type="hidden" name="dealings_idx" value="<?=$dealingsIdx?>">
	<p><h3>[<?=TITLE_VOUCHER_SELL_MODIFY?>]</h3></p>
	<p>
		<label for="dealingsSubject">제목: </label>
		<input type="text" id="dealingsSubject" name="dealings_subject" value="<?=$dealingsSubject?>" size="60">
	</p>
	<p>
		<label for="dealingsContent">내용: </label>
		<input type="text" id="dealingsContent" name="dealings_content" value="<?=$dealingsContent?>" size="60">
	</p>
	<p>
		<label for="itemNo">상품권:</label>
		<?=$itemName?>
		<input type="hidden" id="itemNo" value="<?=$itemNo?>">
	</p>
	<p>
		<label for="itemMoney">상품권 가격:</label>
		<?=number_format($itemMoney)?>원
		<input type="hidden" id="itemMoney" value="<?=$itemMoney?>">
	</p>
	<p>
		<label for="itemObjectNo">상품권 핀번호:</label>
		<input type="text" id="itemObjectNo" name="item_object_no" value="<?=setDecrypt($itemObjectNo)?>" size="25">
	</p>
	<p>
		<label for="">비고:</label>
		<input type="text" id="memo" name="memo" value="<?=$dealingsMemo?>" size="50">
		<input type="hidden" id="commission" name="commission" value="15">
	</p>
	<p><input type="button" id="sell-btn" value="판매수정"></p>
</form>