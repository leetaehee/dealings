<form id="voucher-charge-form" method="post" action="<?=$actionUrl?>">
	<input type="hidden" name="mode" value="<?=$actionMode?>">
	<input type="hidden" name="mileage_type" value="<?=$mileageType?>">
    
    <p><h3>[문화상품권 충전]</h3></p>

	<p>
		<label for="accountNo">상품권번호: </label>
		<input type="text" id="accountNo" name="account_no" value="" size="18">
		<input type="hidden" id="accountBank" name="account_bank" value="문화상품권">
	</p>
	<br>


	<p>
		<label for="chargeCost">충전금액: </label>
		<?php if(count($CONFIG_MILEAGE_ARRAY) > 0):?>
			<select id="chargeCost" name="charge_cost">
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
	</p>
	<br>

	<p>
		<label for="chargeName">입금자: </label>
		<input type="text" id="chargeName" name="charge_name" value="" size="10">
	</p>
	<br>
	
	<p>
		<input type="button" id="charge-btn" value="충전하기">
	</p>
</form>
