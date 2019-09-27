<form id="setting-discount-form" method="post" action="<?=$actionUrl?>">
	<p>
		<h3>[<?=TITLE_COUPON_MAXIMUM_DISCOUNT_RATE?>]</h3>
	</p><br>

	<p>
		<label for="voucher-name">상품권종류: </label>
		<?php if($voucherCount > 0): ?>
			<select id="voucher-name" name="voucher_name">
				<option value="">선택하세요.</option>
				<?php for($i = 0; $i<$voucherCount; $i++): ?>
					<option value="<?=$CONFIG_COUPON_VOUCHER_ARRAY[$i]?>">
						<?=$CONFIG_COUPON_VOUCHER_ARRAY[$i]?>
					</option>
				<?php endfor; ?>
			</select>
		<?php else: ?>
			관리자에게 문의하세요
		<?php endif; ?>
	</p><br>

	<p>
		<label for="voucher-price">상품권 가격: </label>
		<?php if($voucherMoneyCount > 0): ?>
			<select id="voucher-price" name="voucher_price">
				<option value="">선택하세요.</option>
				<?php for($i = 0; $i<$voucherMoneyCount; $i++): ?>
					<option value="<?=$CONFIG_VOUCHER_MONEY_ARRAY[$i]?>">
						<?=$CONFIG_VOUCHER_MONEY_ARRAY[$i]?>
					</option>
				<?php endfor; ?>
			</select>
		<?php endif; ?>
	</p><br>

	<p>
		<label for="discount-rate">최대할인율:</label>
		<input type="text" id="discount-rate" name="discount_rate" value="" size="13"> %
	</p><br>

	<p>
		<input type="button" id="submit-btn" value="<?=$btnName?>">
	</p>

</form>