<form id="dealinges-sell-detail-form" method="post" action="<?=$actionUrl?>">
	<input type="hidden" name="dealings_type" value="<?=$dealingsType?>">
	<p>
		<h3>[<?=TITLE_VOUCHER_SELL_DETAIL_VIEW?>]</h3>
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
		7. 상품권가격: <?=$dealingsData->fields['item_money']?>원
	</p>

	<p>
		8. 상품권 거래금액: <?=$dealingsData->fields['dealings_mileage']?>원 
		(수수료: <?=$dealingsData->fields['dealings_commission']?>%)
	</p>

	<?php if(!empty($dealingsData->fields['memo'])): ?>
		<p>
			9. 비고: <?=$dealingsData->fields['memo']?>
		</p>
	<?php endif;?>

	<p>
		10. 거래상태: <?=$dealingsData->fields['dealings_status_name']?>
	</p>

	<p>
		11. 상품권 핀번호: 결제 및 관리자가 확인 후 문자로 보내드립니다.
	</p>
	
	<?php if($dealingsData->fields['writer_idx'] != $_SESSION['idx']): ?>
		<p>
			<input type="button" id="submit-btn" value="<?=$btnName?>">
		</p>
	<?php endif; ?>
</form>