<form id="vourcher-purchase-form" method="post" action="<?=$actionUrl?>">
	<input type="hidden" name="mode" value="<?=$actionMode?>">
	<input type="hidden" name="dealings_idx" value="<?=$dealingsIdx?>">

	<p>
		<h3>[<?=TITLE_VOUCHER_PURCHASE_MODIFY?>]</h3>
	</p><br>

	<p>
		<label for="">제목: </label>
		<input type="text" id="dealingsSubject" name="dealings_subject" value="<?=$dealingsData->fields['dealings_subject']?>" size="60">
	</p><br>

	<p>
		<label for="">내용: </label>
		<input type="text" id="dealingsContent" name="dealings_content" value="<?=$dealingsData->fields['dealings_content']?>" size="60">
	</p><br>

	<p>
		<label for="">상품권:</label>
		<?php if($vourcherListCount > 0):?>
			<select id="itemNo" name="item_no">
				<option value="">선택하세요.</option>
				<?php foreach($voucherList as $key => $value): ?>
					<option value="<?=$value['idx']?>" class="<?=$value['commission']?>" <?php if($value['idx']==$itemNo){ echo 'selected'; } ?>>
						<?=$value['item_name']?>
					</option>
				<?php endforeach; ?>
			</select>
		<?php else: ?>
			<p>관리자에게 문의하세요!</p>
		<?php endif; ?>
	</p><br>

	<p>
		<label for="">상품권 가격:</label>
		<?php if(count($CONFIG_MILEAGE_ARRAY) > 0):?>
			<select id="itemMoney" name="item_money">
				<option value="">선택하세요.</option>
				<?php for($i=0; $i<count($CONFIG_MILEAGE_ARRAY); $i++): ?>
					<option value="<?=$CONFIG_MILEAGE_ARRAY[$i]?>" <?php if($CONFIG_MILEAGE_ARRAY[$i]==$itemMoney){ echo 'selected'; } ?>>
						<?=$CONFIG_MILEAGE_ARRAY[$i];?>
					</option>
				<?php endfor; ?> 
			</select>원
		<?php else: ?>
			<p>관리자에게 문의하세요!</p>
		<?php endif; ?>
	</p><br>

	<p>
		<label for="">거래금액:</label>
		<input type="text" id="dealingsMileage" name="dealings_mileage" value="" size="15">원
		<span id="display-commission"></span>
	</p><br>

	<p>
		<label for="">비고:</label>
		<input type="text" id="memo" name="memo" value="<?=$dealingsData->fields['memo']?>" size="50">
		<input type="hidden" id="commission" name="commission" value="15">
	</p><br>

	<p>
		<input type="button" id="purchase-btn" value="구매수정">
	</p>
</form>