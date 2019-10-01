<form id="issue-coupon-form" method="post" action="<?=$actionUrl?>">
	<p>
		<h3>[<?=TITLE_COUPON_ISSUE?>]</h3>
	</p><br>

	<p>
		<label for="coupon-issue-type">발행쿠폰타입 </label>
		<select id="coupon-issue-type" name="coupon_issue_type">
			<option value="">선택하세요.</option>
			<option value="구매">구매</option>
			<option value="판매">판매</option>
		</select>
	</p><br>

	<p>
		<label for="coupon-subject">발행쿠폰명칭: </label>
		<input type="text" id="coupon-subject" name="coupon_subject" value="" size="20"> 
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
				<option value="0">모든금액</option>
			</select>
		<?php else: ?>
			관리자에게 문의하세요
		<?php endif; ?>
	</p><br>

	<p>
		<label for="start-date">쿠폰 적용일자: </label>
		<input type="text" id="start-date" name="start_date" value="" size="15"> 
	</p><br>

	<p>
		<label for="expiration-date">쿠폰 만료일자: </label>
		<input type="text" id="expiration-date" name="expiration_date" value="" size="15">
	</p><br>

	<p>
		<label for="discount-rate">할인율:</label>
		<input type="text" id="discount-rate" name="discount_rate" value="" size="13"> % 
		(발행쿠폰타입이 '판매'인 경우 할인율은 수수료를 기준으로 계산됩니다.)
	</p><br>

	<p>
		<input type="button" id="submit-btn" value="<?=$btnName;?>">
	</p>

</form>