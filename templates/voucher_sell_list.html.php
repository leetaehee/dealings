<p>
	<h3>[<?=TITLE_VOUCHER_SELL_LIST?>]</h3>
</p>
<table class="table dealings-table-width">
	<colgroup>
		<col style="width: 6%;">
		<col style="width: 16%;">
		<col style="width: 39%;">
		<col style="width: 13%;">
		<col style="width: 13%;">
		<col style="width: 13%;">
	</colgroup>
	<thead>
		<tr>
			<th>순번</th>
			<th>분류</th>
			<th>제목</th>
			<th>등록일</th>
			<th>물품금액</th>
			<th>거래금액</th>
		</tr>
	</thead>
	<tbody>
		<?php if($sellDataCount > 0): ?>
			<?php foreach($sellData as $key => $value): ?>
				<tr>
					<td><?=$value['seq']?></td>
					<td><?=$value['item_name']?></td>
					<td>
						<a href="<?=$value['detail_view_url']?>">
							<?=$value['dealings_subject']?>
						</a>
					</td>
					<td><?=$value['register_date']?></td>
					<td><?=number_format($value['item_money'])?></td>
					<td><?=number_format($value['dealings_mileage'])?></td>
				</tr>
			<?php endforeach; ?>
		<?php else: ?>
			<tr>
				<td colspan="6" class="empty-tr-colspan">내역이 없습니다.</td>
			</tr>
		<?php endif; ?>
	</tbody>
</table>