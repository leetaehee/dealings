<p>
	<h3>[<?=TITLE_COUPON_MODIFY?>]</h3>
</p>
<table class="table dealings-table-width">
	<colgroup>
		<col style="width: 6%;">
		<col style="width: 6%;">
		<col style="width: 30%;">
		<col style="width: 21%;">
		<col style="width: 15%;">
		<col style="width: 11%;">
		<col style="width: 11%;">
	</colgroup>
	<thead>
		<tr>
			<th>순번</th>
			<th>구분</th>
			<th>발행쿠폰명칭</th>
			<th>상품권종류</th>
			<th>결제금액</th>
			<th>할인율</th>
			<th>쿠폰변경</th>
		</tr>
	</thead>
	<tbody>
		<?php if($couponListCount > 0): ?>
			<?php foreach($couponList as $key => $value): ?>
				<tr>
					<td><?=$key+1?></td>
					<td><?=$value['issue_type']?></td>
					<td><?=$value['subject']?></td>
					<td><?=$value['item_name']?></td>
					<td><?=$value['item_money']?>원</td>
					<td><?=$value['discount_rate']?>%</td>
					<td>
						<a href="<?=$couponUpdateURL?>?idx=<?=$idx?>&coupon_idx=<?=$value['idx']?>">
							[변경하기]
						</a>
					</td>

				</tr>
			<?php endforeach; ?>
		<?php else: ?>
			<tr>
				<td colspan="7" class="empty-tr-colspan">내역이 없습니다.</td>
			</tr>
		<?php endif; ?>
	</tbody>
</table>