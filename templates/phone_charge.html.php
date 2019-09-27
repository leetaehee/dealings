<form id="phone-charge-form" method="post" action="<?=$actionUrl?>">
    <p><h3>[휴대전화 충전]</h3></p>

	<p>
		<label for="accountBank">통신사종류: </label>
		<select id="accountBank" name="account_bank">
			<option value="">선택하세요.</option>
			<option value="KT">KT</option>
			<option value="LGU">LGU</option>
			<option value="SKT">SKT</option>
		</select>
	</p>
	<br>

	<p>
		<label for="accountNo">휴대전화: </label>
		<input type="text" id="accountNo" name="account_no" value="" size="22">
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
