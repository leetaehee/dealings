<form id="my-purchase-status-form" method="post" action="<?=$actionUrl?>">
	<input type="hidden" name="dealings_type" value="<?=$dealingsType?>">
	<p>
		<h3>[<?=TITLE_VOUCHER_PURCHASE_ENROLL_STATUS?>]</h3>
	</p>

	<p>---------------------------------------</p>

	<p>
		1. 제목: <?=$dealingsData->fields['dealings_subject']?>
	</p>

	<p>
		2. 내용: <?=$dealingsData->fields['dealings_content']?>
	</p>

	<p>
		3. 구매등록일 : <?=$dealingsData->fields['register_date']?>
	</p>

	<p>
		4. 구매종료일 : <?=$dealingsData->fields['expiration_date']?>
	</p>

	<p>
		5. 구매자 정보: 
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

	<p>
		10. 상품권 핀번호: 결제 및 관리자가 확인 후 문자로 보내드립니다.
	</p>

	<?php if(!empty($dealingsData->fields['memo'])): ?>
		<p>
			11. 비고: <?=$dealingsData->fields['memo']?>
		</p>
	<?php endif;?>

	<p>
		12. 이용가능한 마일리지: <?=number_format($totalMileage)?>원
		<input type="hidden" id="purchaser-mileage" value="<?=$totalMileage?>">
	</p>

	<p>
        <?php if (!empty($couponIdx)): ?>
            13. 최종결제금액: <?=number_format($useCouponData->fields['coupon_use_mileage'])?>원 (쿠폰적용 할인)
        <?php else: ?>
            13. 최종 결제 금액: <span id="finalPaymentSum"><?=number_format($dealingsMileage);?></span>원
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
						<option value="<?=$couponData->fields['idx']?>" data-discount_rate="<?=$couponData->fields['discount_rate']?>">
							<?=$couponData->fields['subject']?>(<?=$couponData->fields['discount_rate']?>%)
						</option>	
					<?php endforeach;?>
				</select>
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

		<?php if($dealingsData->fields['dealings_status'] == 2): ?>
			<p>
				<input type="button" id="submit-btn" value="<?=$btnName?>">
			</p>
		<?php endif ?>
	<?php else: ?>
		<?php if (!empty($couponIdx)): ?>
			<p>
				---------------------------------------
			</p>
			<p>
				[쿠폰 사용 내용]
			</p>
			<p>
				1. 사용한 쿠폰 정보: "<?=$useCouponData->fields['subject']?>" <br>
			</p>
            <p>
                2. 쿠폰할인율 :
				<?php if($useCouponData->fields['item_money'] == 0): ?>
					모든금액 적용쿠폰
				<?php else: ?>
					<?=$useCouponData->fields['item_money']?>원
				<?php endif; ?>
				(<?=$useCouponData->fields['discount_rate']?>% 쿠폰)
            </p>
			<p>
				3. 할인금액: 
				<?php if($useCouponData->fields['item_money'] == 0): ?>
					<?=number_format(($dealingsData->fields['dealings_mileage']*$useCouponData->fields['discount_rate'])/100)?>원
				<?php else: ?>
					<?=number_format($useCouponData->fields['discount_money'])?>원
				<?php endif; ?>
			</p>
		<?php endif; ?>
	<?php endif; ?>

	<?php if($dealingsData->fields['dealings_status'] > 1): ?>
		<p>
			---------------------------------------
		</p>
		<p>
			[판매자정보]
		</p>
		<p>
			1. 이름: <?=setDecrypt($purchaserData->fields['name'])?>
			(<?=$purchaserData->fields['id']?>)
			<input type="hidden" name="dealings_writer_idx" value="<?=$purchaserData->fields['idx']?>">
		</p>
		<p>
			2. 연락처 <?=setDecrypt($purchaserData->fields['phone'])?>
		</p>
		<p>
			3. 이메일: <?=setDecrypt($purchaserData->fields['email'])?>
		</p>
		<p>
			---------------------------------------
		</p>
	<?php endif; ?>

	<?php if($dealingsData->fields['dealings_status'] < 2): ?>
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