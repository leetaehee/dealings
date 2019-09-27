<form id="vourcher-sell-form" method="post" action="<?=$actionUrl?>">
	<input type="hidden" name="dealings_state" value="<?=$dealingsState?>">
	<input type="hidden" name="dealings_type" value="<?=$dealingsType?>">

	<p>
		<h3>[<?=TITLE_VOUCHER_SELL_ENROLL?>]</h3>
	</p><br>

	<p>
		<label for="dealingsSubject">제목: </label>
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
		<label for="itemObjectNo">상품권 핀번호:</label>
		<input type="text" id="itemObjectNo" name="item_object_no" value="" size="25">
	</p><br>

	<p>
		<label for="dealingsMileage">거래금액:</label>
		<input type="text" id="dealingsMileage" name="dealings_mileage" value="" size="15">원
		<span id="display-commission"></span>
	</p><br>

	<p>
		<label for="">비고:</label>
		<input type="text" id="memo" name="memo" value="" size="50">
	</p><br>

	<?php if($couponDataCount > 0): ?>
		<p>
			---------------------------------------
		</p>
		<p>
			[사용 가능한 쿠폰정보]
		</p>
		<p>
			1. 쿠폰선택: 
			<select id="coupon-name" name="coupon_name">
				<option value="">선택하세요.</option>
				<?php foreach($couponData as $key => $value): ?>
					<option value="<?=$couponData->fields['idx']?>" data-discount_mileage="<?=$couponData->fields['discount_mileage']?>">
						<?=$couponData->fields['subject']?>(<?=$couponData->fields['discount_rate']?>%)
					</option>	
				<?php endforeach;?>
			</select>
			<br>(쿠폰을 사용하실 경우 판매 수수료에 할인을 받을 수 있습니다.)
		</p>
	<?php else: ?>
		<p>
			---------------------------------------
		</p>
		<p>
			[사용 가능한 쿠폰정보]
		</p>
		<p>
			1. 쿠폰선택: 적용 할 쿠폰이 없습니다.
		</p>
	<?php endif; ?>

	<p>
		<input type="button" id="sell-btn" value="판매등록">
	</p>
</form>