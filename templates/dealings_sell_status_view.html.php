<form id="dealinges-sell-status-form" method="post" action="<?=$actionUrl?>">
	<input type="hidden" name="dealings_type" value="<?=$dealingsType?>">
	<p><h3>[<?=TITLE_VOUCHER_SELL_STATUS?>]</h3></p>
	<p>---------------------------------------</p>
	<p>1. 제목: <?=$dealingsSubject?></p>
	<p>2. 내용: <?=$dealingsContent?></p>
	<p>3. 판매등록일 : <?=$registerDate?></p>
	<p>4. 판매종료일 : <?=$expirationDate?></p>
	<p>5. 판매자 정보:<?=setDecrypt($name)?>(<?=$id?>)</p>
	<p>6. 상품권분류: <?=$itemName?></p>
	<p>7. 상품권가격: <?=number_format($itemMoney)?>원</p>
	<p>
		8. 상품권 거래금액: <?=number_format($dealingsMileage)?>원
		<input type="hidden" id="dealings-mileage" name="dealings_mileage" value="<?=$dealingsMileage?>">
	</p>
	<p>9. 거래상태: <?=$dealingsStatusName?></p>
	<?php if(!empty($itemObjectNo)): ?>
		<?php if($dealingsStatus==4): ?>
			<p>10. 상품권 핀번호: <?=setDecrypt($itemObjectNo)?></p>
		<?php else: ?>
			<p>10. 상품권 핀번호: 핀번호는 결제를 진행한 후 관리자가 승인해야 노출됩니다.</p>
		<?php endif; ?>
	<?php endif; ?>
    <p>
        <?php if (!empty($couponIdx)): ?>
            11. 최종결제금액: <?=number_format($couponUseMileage)?>원 (쿠폰적용 할인)
        <?php else: ?>
            11. 최종 결제 금액: <span id="finalPaymentSum"><?=number_format($dealingsMileage);?></span>원
        <?php endif; ?>
    </p>

	<?php if ($dealingsStatus == 2): ?>
		<?php if($couponMbResultCount > 0): ?>
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
					<?php foreach($rCouponMbResult as $key => $value): ?>
						<option value="<?=$value['idx']?>" data-discount_rate="<?=$value['discount_rate']?>">
							<?=$value['subject']?>(<?=$value['discount_rate']?>%)
						</option>	
					<?php endforeach;?>
				</select>
				<br>(쿠폰을 사용하실 경우 판매 수수료에 할인을 받을 수 있습니다.)
			</p>
		<?php else: ?>
			<p>---------------------------------------</p>
			<p>[사용 가능한 쿠폰정보]</p>
			<p>1. 쿠폰선택: 적용 할 쿠폰이 없습니다.</p>
		<?php endif; ?>

		<p>
			---------------------------------------
		</p>

		<?php if(!empty($purchaserId)): ?>
			<p>
				[구매자정보]
			</p>
			<p>
				1. 이름: <?=setDecrypt($purchaserName)?>(<?=$purchaserId?>)
				<input type="hidden" value="<?=$purchaserIdx?>">
			</p>
			<p>
				2. 연락처 <?=setDecrypt($purchaserPhone)?>
			</p>
			<p>
				3. 이메일: <?=setDecrypt($purchaserEmail)?>
			</p>
			<p>
				4. 이용가능한 마일리지: <?=number_format($purchaserMileage)?>원
				<input type="hidden" id="purchaser-mileage" value="<?=$purchaserMileage?>">
			</p>
		<?php endif; ?>

		<?php if($dealingsStatus == 2): ?>
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
				1. 사용한 쿠폰정보: "<?=$couponSubject?>"
			</p>
			<p>
				2. 쿠폰할인율 : 
				<?php if($couponItemMoney == 0): ?>
					모든금액 적용쿠폰
				<?php else: ?>
					<?=$couponItemMoney?>원
				<?php endif; ?>
				(<?=$couponDiscountRate?>% 쿠폰)
            </p>
			<p>
				3. 할인금액: 
				<?php if($couponItemMoney == 0): ?>
					<?=number_format(($dealingsMileage * $couponDiscountRate)/100)?>원
				<?php else: ?>
					<?=number_format($couponDiscountMoney)?>원
				<?php endif; ?>
			</p>
		<?php endif; ?>
	<?php endif; ?>
</form>

<script>
var dealingsMileage = <?=$dealingsMileage?>;
</script>