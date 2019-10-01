<form id="vourcher-purchase-form" method="post" action="<?=$actionUrl?>">
	<input type="hidden" name="dealings_state" value="<?=$dealingsState?>">
	<input type="hidden" name="dealings_type" value="<?=$dealingsType?>">

	<p>
		<h3>[<?=TITLE_VOUCHER_PURCHASE_ENROLL?>]</h3>
	</p><br>

	<p>
		<label for="">제목: </label>
		<input type="text" id="dealingsSubject" name="dealings_subject" value="" size="60">
	</p><br>

	<p>
		<label for="dealingsContent">내용: </label>
		<input type="text" id="dealingsContent" name="dealings_content" value="" size="60">
	</p><br>

	<p>
		<label for="itemNo">상품권:</label>
		<?php if($vourcherListCount > 0):?>
			<select id="itemNo" name="item_no">
				<option value="">선택하세요.</option>
				<?php foreach($voucherList as $key => $value): ?>
					<option value="<?=$value['idx']?>" class="<?=$value['commission']?>">
						<?=$value['item_name']?>
					</option>
				<?php endforeach; ?>
			</select>
		<?php else: ?>
			<p>관리자에게 문의하세요!</p>
		<?php endif; ?>
	</p><br>

	<p>
		<label for="itemMoney">상품권 가격:</label>
		<?php if(count($CONFIG_MILEAGE_ARRAY) > 0):?>
			<select id="itemMoney" name="item_money">
				<option value="">선택하세요.</option>
				<?php for($i=0; $i<count($CONFIG_MILEAGE_ARRAY); $i++): ?>
					<option value="<?=$CONFIG_MILEAGE_ARRAY[$i]?>">
						<?=$CONFIG_MILEAGE_ARRAY[$i];?>
					</option>
				<?php endfor; ?> 
			</select>원
		<?php else: ?>
			<p>관리자에게 문의하세요!</p>
		<?php endif; ?>
	</p><br>

	<p>
		<label for="dealingsMileage">거래금액:</label>
		<input type="text" id="dealingsMileage" name="dealings_mileage" value="" size="15">원
	</p><br>

	<p>
		<label for="memo">비고:</label>
		<input type="text" id="memo" name="memo" value="" size="50">
	</p><br>

	<p>
		<input type="button" id="purchase-btn" value="구매등록">
	</p>
</form>