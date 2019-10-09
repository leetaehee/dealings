<p>
	<h3>[<?=TITLE_COUPON_PROVIDER_STATUS?>]</h3>
</p>
<p>
	- 모든상품권/금액 쿠폰은 결제금액이 0원으로 표시 됩니다.
</p>
<p>
	- 실제 거래되는 물건 거래금액에 할인율을 계산하면됩니다.
</p>
<table class="table dealings-table-width">
	<colgroup>
		<col style="width: 6%;">
		<col style="width: 6%;">
		<col style="width: 28%;">
		<col style="width: 20%;">
		<col style="width: 10%;">
		<col style="width: 9%;">
		<col style="width: 9%;">
		<col style="width: 6%;">
		<col style="width: 6%;">
	</colgroup>
	<thead>
		<tr>
			<th>순번</th>
			<th>구분</th>
			<th>발행쿠폰명칭</th>
			<th>상품권종류</th>
			<th>결제금액</th>
			<th>할인율</th>
			<th>사용금액</th>
			<th>수정</th>
			<th>삭제</th>
		</tr>
	</thead>
	<tbody>
		<?php if($couponProviderListCount > 0): ?>
			<?php foreach($couponProviderList as $key => $value): ?>
				<tr>
					<td><?=$key+1?></td>
					<td><?=$value['issue_type']?></td>
					<td><?=$value['subject']?></td>
					<td><?=$value['item_name']?></td>
					<td><?=$value['item_money']?>원</td>
					<td><?=$value['discount_rate']?>%</td>
					<td>
						<?php if(!empty($value['use_idx'])): ?>
							<?=$value['coupon_use_mileage']?>원
						<?php else: ?>
							0원
						<?php endif; ?>
					</td>
					<td>
						<?php if(empty($value['use_idx'])): ?>
							<a href="<?=$couponUpdateURL?>?idx=<?=$value['idx']?>&coupon_idx=<?=$value['coupon_idx']?>&member_idx=<?=$getData['idx']?>">
								[수정]
							</a>
						<?php endif; ?>
					</td>
					<td>
						<?php if($value['is_del']=='Y'): ?>
							<p>지급삭제</p>
						<?php else: ?>
							<?php if(empty($value['use_idx'])): ?>
								<a href="<?=$couponDeleteURL?>?idx=<?=$value['idx']?>&coupon_idx=<?=$value['coupon_idx']?>&member_idx=<?=$getData['idx']?>">
									[삭제]
								</a>
							<?php endif; ?>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		<?php else: ?>
			<tr>
				<td colspan="9" class="empty-tr-colspan">내역이 없습니다.</td>
			</tr>
		<?php endif; ?>
	</tbody>
</table>