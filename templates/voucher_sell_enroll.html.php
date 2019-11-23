<form id="vourcher-sell-form" method="post" action="<?=$actionUrl?>">
	<input type="hidden" name="dealings_state" value="<?=$dealingsState?>">
	<input type="hidden" name="dealings_type" value="<?=$dealingsType?>">

	<p><h3>[<?=TITLE_VOUCHER_SELL_ENROLL?>]</h3></p>
	<p>
		<label for="dealingsSubject">제목: </label>
		<input type="text" id="dealingsSubject" name="dealings_subject" value="<?=$subject?>" size="60">
	</p>
	<p>
		<label for="dealingsContent">내용: </label>
		<input type="text" id="dealingsContent" name="dealings_content" value="<?=$content?>" size="60">
	</p>
	<p>
		<label for="itemNo">상품권:</label>
		<?php if($rSellItemCount > 0):?>
			<select id="itemNo" name="item_no">
				<option value="">선택하세요.</option>
				<?php foreach($rSellItemResult as $key => $value): ?>
					<option value="<?=$value['idx']?>"
                            class="<?=$value['commission']?>"
                            <?php echo $value['idx'] == $itemNo ? 'selected' : ''; ?>>
						<?=$value['item_name']?>
					</option>
				<?php endforeach; ?>
			</select>
		<?php else: ?>
			<p>관리자에게 문의하세요!</p>
		<?php endif; ?>
	</p>
	<p>
		<label for="itemMoney">상품권 가격:</label>
		<?php if(count($CONFIG_MILEAGE_ARRAY) > 0):?>
			<select id="itemMoney" name="item_money">
				<option value="">선택하세요.</option>
				<?php for($i=0; $i<count($CONFIG_MILEAGE_ARRAY); $i++): ?>
					<option value="<?=$CONFIG_MILEAGE_ARRAY[$i]?>"
                            <?php echo $CONFIG_MILEAGE_ARRAY[$i] == $itemMoney ? 'selected' : '';?>>
						<?=$CONFIG_MILEAGE_ARRAY[$i];?>
					</option>
				<?php endfor; ?> 
			</select>원
		<?php else: ?>
			<p>관리자에게 문의하세요!</p>
		<?php endif; ?>
	</p>
	<p>
		<label for="itemObjectNo">상품권 핀번호:</label>
		<input type="text" id="itemObjectNo" name="item_object_no" value="<?=$itemObjectNo?>" size="25">
	</p>
	<p>
		<label for="dealingsMileage">거래금액:</label>
		<input type="text" id="dealingsMileage" name="dealings_mileage" value="<?=$mileage?>" size="15">원
		<span id="display-commission"></span>
	</p>
	<p>
		<label for="memo">비고:</label>
		<input type="text" id="memo" name="memo" value="<?=$memo?>" size="50">
	</p>
	<?php if($couponDataCount > 0): ?>
		<p>---------------------------------------</p>
		<p>[사용 가능한 쿠폰정보]</p>
		<p>
			1. 쿠폰선택: 
			<select id="coupon-name" name="coupon_name">
				<option value="">선택하세요.</option>
				<?php foreach($rAvailableCouponResult as $key => $value): ?>
					<option value="<?=$value['idx']?>"
                            data-discount_mileage="<?=$value['discount_mileage']?>">
                            <?=$value['subject']?>(<?=$value['discount_rate']?>%)
					</option>	
				<?php endforeach;?>
			</select><br>
			(쿠폰이 조회되지 않을때는 상품권과 상품권금액을 선택하시면 해당되는 쿠폰이 조회됩니다.<br>
		</p>
	<?php else: ?>
		<p>---------------------------------------</p>
        <p>[사용 가능한 쿠폰정보]</p>
		<p>1. 쿠폰선택: 적용 할 쿠폰이 없습니다.</p>
	<?php endif; ?>
	<p><input type="button" id="sell-btn" value="판매등록"></p>
</form>