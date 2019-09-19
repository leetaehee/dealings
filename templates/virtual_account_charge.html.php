<form id="virtual-charge-form" method="post" action="<?=$actionUrl?>">
	<input type="hidden" name="mode" value="<?=$actionMode?>">
	<input type="hidden" name="mileage_type" value="<?=$mileageType?>">
    
    <p><h3>[가상계좌 충전]</h3></p>

	<p>
		<label for="accountBank">입금은행: </label>
		<?php if(count($CONFIG_BANK_ARRAY) > 0):?>
			<select id="accountBank" name="account_bank">
				<option value="">선택하세요.</option>
				<?php for($i=0; $i<count($CONFIG_BANK_ARRAY); $i++): ?>
					<option value="<?=$CONFIG_BANK_ARRAY[$i]?>">
						<?=$CONFIG_BANK_ARRAY[$i];?>
					</option>
				<?php endfor; ?>
			</select>
		<?php else: ?>
			<p>관리자에게 문의하세요!</p>
		<?php endif; ?>
		<input type="button" id="issue-btn" value="계좌조회">
	</p>
	<br>

	<p>
		계좌번호: <span id="accountNo">은행을 선택하세요</span>
		<input type="hidden" class="accountNo" name="account_no" value="">
	</p>
	<br>

	<p>
		<label for="chargeCost">입금금액: </label>
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
