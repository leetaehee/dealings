<form id="dealinges-sell-status-form" method="post" action="<?=$actionUrl?>">
	<input type="hidden" name="dealings_type" value="<?=$dealingsType?>">
	<p>
		<h3>[<?=TITLE_VOUCHER_SELL_STATUS?>]</h3>
	</p>

	<p>---------------------------------------</p>

	<p>
		1. 제목: <?=$dealingsData->fields['dealings_subject']?>
	</p>

	<p>
		2. 내용: <?=$dealingsData->fields['dealings_content']?>
	</p>

	<p>
		3. 판매등록일 : <?=$dealingsData->fields['register_date']?>
	</p>

	<p>
		4. 판매종료일 : <?=$dealingsData->fields['expiration_date']?>
	</p>

	<p>
		5. 판매자 정보: 
		<?=setDecrypt($dealingsData->fields['name'])?>
		(<?=$dealingsData->fields['id']?>)
	</p>

	<p>
		6. 상품권분류: <?=$dealingsData->fields['item_name']?>
	</p>

	<p>
		7. 상품권가격: <?=number_format($dealingsData->fields['item_money'])?>원
	</p>

	<p>
		8. 상품권 거래금액: <?=number_format($dealingsData->fields['dealings_mileage'])?>원
		<input type="hidden" id="dealings-mileage" name="dealings_mileage" value="<?=$dealingsData->fields['dealings_mileage']?>">
	</p>

	<p>
		9. 거래상태: <?=$dealingsData->fields['dealings_status_name']?>
	</p>

	<?php if(!empty($dealingsData->fields['item_object_no'])): ?>
		<?php if($dealingsData->fields['dealings_status']==4): ?>
			<p>
				10. 상품권 핀번호: <?=setDecrypt($dealingsData->fields['item_object_no'])?>
			</p>
		<?php else: ?>
			<p>
				10. 상품권 핀번호: 핀번호는 결제를 진행한 후 관리자가 승인해야 노출됩니다.
			</p>
		<?php endif; ?>
	<?php endif; ?>

    <p>
        <?php if (!empty($couponIdx)): ?>
            11. 최종결제금액: <?=number_format($useCouponData->fields['coupon_use_mileage'])?>원 (쿠폰적용 할인)
        <?php else: ?>
            11. 최종 결제 금액: <span id="finalPaymentSum"><?=number_format($dealingsMileage);?></span>원
        <?php endif; ?>
    </p>

	<?php if ($dealingsData->fields['dealings_status'] == 2): ?>
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
			---------------------------------------
		</p>

		<?php if($purchaserDataCount > 0): ?>
			<p>
				[구매자정보]
			</p>
			<p>
				1. 이름: <?=setDecrypt($purchaserData->fields['name'])?>
				(<?=$purchaserData->fields['id']?>)
				<input type="hidden" value="<?=$purchaserData->fields['idx']?>">
			</p>
			<p>
				2. 연락처 <?=setDecrypt($purchaserData->fields['phone'])?>
			</p>
			<p>
				3. 이메일: <?=setDecrypt($purchaserData->fields['email'])?>
			</p>
			<p>
				4. 이용가능한 마일리지: <?=number_format($purchaserData->fields['mileage'])?>원
				<input type="hidden" id="purchaser-mileage" value="<?=$purchaserData->fields['mileage']?>">
			</p>
		<?php endif; ?>

		<?php if($dealingsData->fields['dealings_status'] == 2): ?>
			<p>
				<input type="button" id="submit-btn" value="<?=$btnName?>">
			</p>
		<?php endif; ?>
	<?php else: ?>
		<?php if (!empty($couponIdx)): ?>
			<p>
				---------------------------------------
			</p>
			<p>
				[쿠폰 사용 내용]
			</p>
			<p>
				1. 사용한 쿠폰명: "<?=$useCouponData->fields['subject']?>" 
				<?=$useCouponData->fields['item_money']?>원
				(<?=$useCouponData->fields['discount_rate']?>% 쿠폰)
			</p>
			<p>
                2. 쿠폰할인율: <?=$useCouponData->fields['item_money']?>원
				(<?=$useCouponData->fields['discount_rate']?>% 쿠폰)
            </p>
			<p>
				3. 할인금액: <?=number_format($useCouponData->fields['discount_money'])?>원
			</p>
		<?php endif; ?>
	<?php endif; ?>
</form>

<script>
var dealingsMileage = <?=$dealingsMileage?>;
</script>