<form id="my-purchase-status-form" method="post" action="<?=$actionUrl?>">
	<input type="hidden" name="dealings_type" value="<?=$dealingsType?>">
	<p><h3>[<?=TITLE_VOUCHER_PURCHASE_ENROLL_STATUS?>]</h3></p>
	<p>---------------------------------------</p>
	<p>1. 제목: <?=$dealingsSubject?></p>
	<p>2. 내용: <?=$dealingsContent?></p>
	<p>3. 구매등록일 : <?=$registerDate?></p>
	<p>4. 구매종료일 : <?=$expirationDate?></p>
	<p>
		5. 구매자 정보: 
		<?=setDecrypt($purchaserName)?>(<?=$purchaserId?>)
	</p>
	<p>6. 상품권분류: <?=$itemName?></p>
	<p>7. 상품권가격: <?=number_format($itemMoney)?>원</p>
	<p>8. 상품권 거래금액: <?=number_format($dealingsMileage)?>원
		<input type="hidden" id="dealings-mileage" name="dealings_mileage" value="<?=$dealingsMileage?>">
	</p>
	<p>9. 거래상태: <?=$dealingsStatusName?></p>
	<p>10. 상품권 핀번호: 결제 및 관리자가 확인 후 문자로 보내드립니다.</p>
	<?php if(!empty($dealingsMemo)): ?>
		<p>11. 비고: <?=$dealingsMemo?></p>
	<?php endif;?>
	<p>
		12. 이용가능한 마일리지: <?=number_format($totalMileage)?>원
		<input type="hidden" id="purchaser-mileage" value="<?=$totalMileage?>">
	</p>

	<p>
        <?php if (!empty($useCouponIdx)): ?>
            13. 최종결제금액: <?=number_format($useCpUsedMileage)?>원 (쿠폰적용 할인)
        <?php else: ?>
            13. 최종 결제 금액: <span id="finalPaymentSum"><?=number_format($dealingsMileage);?></span>원
        <?php endif; ?>
    </p>

	<?php if ($dealingsStatus == 2): ?>
		<?php if($couponDataCount > 0): ?>
			<p>---------------------------------------</p>
			<p>[사용 가능한 쿠폰정보]</p>
			<p>
				1. 쿠폰선택: 
				<select id="coupon-name" name="coupon_name">
					<option value="">선택하세요.</option>
					<?php foreach($couponData as $key => $value): ?>
						<option value="<?=$value['idx']?>" data-discount_rate="<?=$value['discount_rate']?>">
							<?=$value['subject']?>(<?=$value['discount_rate']?>%)
						</option>	
					<?php endforeach;?>
				</select>
			</p>
		<?php else: ?>
			<p>---------------------------------------</p>
			<p>[사용 가능한 쿠폰정보]</p>
			<p>1. 쿠폰선택: 적용 할 쿠폰이 없습니다.</p>
		<?php endif; ?>
		<p>---------------------------------------</p>

		<?php if($dealingsStatus == 2): ?>
			<p><input type="button" id="submit-btn" value="<?=$btnName?>"></p>
		<?php endif ?>
	<?php else: ?>
		<?php if (!empty($useCouponIdx)): ?>
			<p>---------------------------------------</p>
			<p>[쿠폰 사용 내용]</p>
			<p>1. 사용한 쿠폰 정보: "<?=$useCpSubject?>" <br></p>
            <p>
                2. 쿠폰할인율 :
				<?php if($useCpItemMoney== 0): ?>
					모든금액 적용쿠폰
				<?php else: ?>
					<?=$useCpItemMoney?>원
				<?php endif; ?>
				(<?=$useCpDiscountRate?>% 쿠폰)
            </p>
			<p>3. 할인금액: <?=number_format($useCpDiscountMoney)?>원gi</p>
		<?php endif; ?>
	<?php endif; ?>

	<?php if($dealingsStatus > 1): ?>
		<p>---------------------------------------</p>
		<p>[판매자정보]</p>
		<p>
			1. 이름: <?=setDecrypt($sellerName)?>(<?=$sellerId?>)
			<input type="hidden" name="dealings_writer_idx" value="<?=$sellerIdx?>">
		</p>
		<p>2. 연락처 <?=setDecrypt($sellerPhone)?></p>
		<p>3. 이메일: <?=setDecrypt($sellerEmail)?></p>
		<p>---------------------------------------</p>
	<?php endif; ?>

	<?php if($dealingsStatus < 2): ?>
		<span>
			<a href="<?=$dealingsModifyUrl?>">[수정]</a>
		</span>
		<span class="pl">
			<a href="<?=$dealingsDeleteUrl?>">[삭제]</a>
		</span>
	<?php endif; ?>
</form>

<script>
var dealingsMileage = <?=$dealingsMileage?>;
</script>