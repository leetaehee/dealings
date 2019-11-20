<p>
	<h3>[<?=TITLE_VOUCHER_PURCHASE_ENROLL_STATUS?>]</h3>
</p>
<table class="table dealings-table-width">
	<colgroup>
		<col style="width: 6%;">
		<col style="width: 14%;">
		<col style="width: 31%;">
		<col style="width: 13%;">
		<col style="width: 13%;">
		<col style="width: 13%;">
		<col style="width: 10%;">
	</colgroup>
	<thead>
		<tr>
			<th>순번</th>
			<th>분류</th>
			<th>제목</th>
			<th>거래일자</th>
			<th>물품금액</th>
			<th>거래금액</th>
			<th>결제상태</th>
		</tr>
	</thead>
	<tbody>
		<?php if($myPurchaseData > 0): ?>
			<?php foreach($myPurchaseData as $key => $value): ?>
				<tr>
					<td><?=$value['seq']?></td>
					<td><?=$value['item_name']?></td>
					<td>
						<a href="<?=$value['url']?>">
							<?=$value['dealings_subject']?>
						</a>
					</td>
					<td><?=$value['dealings_date']?></td>
					<td><?=number_format($value['item_money'])?></td>
					<td><?=number_format($value['dealings_mileage'])?></td>
					<td><?=$value['dealings_status_name'];?></td>
				</tr>
			<?php endforeach; ?>
		<?php else: ?>
			<tr>
				<td colspan="7" class="empty-tr-colspan">내역이 없습니다.</td>
			</tr>
		<?php endif; ?>
	</tbody>
</table>