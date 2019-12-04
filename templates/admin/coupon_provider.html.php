<p>
	<h3>[<?=TITLE_COUPON_PROVIDER?>]</h3>
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
		<col style="width: 24%;">
		<col style="width: 10%;">
		<col style="width: 9%;">
		<col style="width: 9%;">
		<col style="width: 8%;">
	</colgroup>
	<thead>
		<tr>
			<th>순번</th>
			<th>구분</th>
			<th>발행쿠폰명칭</th>
			<th>상품권종류</th>
			<th>결제금액</th>
			<th>할인율</th>
			<th>할인금액</th>
			<th>추가</th>
		</tr>
	</thead>
	<tbody>
		<?php if($issueCouponDataCount > 0): ?>
			<?php foreach($issueCouponData as $key => $value): ?>
				<tr>
					<td><?=$value['seq']?></td>
					<td><?=$value['issue_type']?></td>
					<td><?=$value['subject']?></td>
					<td><?=$value['item_name']?></td>
					<td><?=$value['item_money']?>원</td>
					<td><?=$value['discount_rate']?>%</td>
					<td><?=$value['discount_mileage']?>원</td>
					<td>
						<a href="<?=$value['coupon_add_url']?>">
							[지급]
						</a>
					</td>
				</tr>
			<?php endforeach; ?>
		<?php else: ?>
			<tr>
				<td colspan="8" class="empty-tr-colspan">내역이 없습니다.</td>
			</tr>
		<?php endif; ?>
	</tbody>
</table>