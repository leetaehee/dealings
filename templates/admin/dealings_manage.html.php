<p>
	<h3>[<?=TITLE_ADMIN_DEALINGS_STATUS?>]</h3>
</p>
<p>
	<h5>-거래완료를 하실 경우에는 취소 하실수 없습니다. 참고하세요.</h5>
</p>
<table class="table dealings-table-width">
	<colgroup>
		<col style="width: 6%;">
		<col style="width: 10%;">
		<col style="width: 33%;">
		<col style="width: 14%;">
		<col style="width: 10%;">
		<col style="width: 11%;">
		<col style="width: 8%;">
		<col style="width: 8%;">
	</colgroup>
	<thead>
		<tr>
			<th>순번</th>
			<th>분류</th>
			<th>제목</th>
			<th>작성자</th>
			<th>거래일자</th>
			<th>거래금액</th>
			<th>거래완료</th>
			<th>거래취소</th>
		</tr>
	</thead>
	<tbody>
		<?php if($payDealingsListCount > 0): ?>
			<?php foreach($payDealingsList as $key => $value): ?>
				<tr>
					<td><?=$key+1?></td>
					<td><?=$value['item_name']?></td>
					<td>[<?=$value['dealings_type']?>]<br><?=$value['dealings_subject']?></td>
					<td>
						<?=setDecrypt($value['name'])?><br>
						(<?=$value['id']?>)
					</td>
					<td><?=$value['dealings_date']?></td>
					<td><?=number_format($value['dealings_mileage'])?></td>
					<td>
						<?php if($value['dealings_status']==3): ?>
							<?php if($value['dealings_type']=='구매'): ?>
								<a href="<?=$DealingsDetailViewHref;?>&idx=<?=$value['dealings_idx']?>&member_idx=<?=$value['dealings_member_idx']?>">
									완료
								</a>
							<?php else: ?>
								<a href="<?=$DealingsDetailViewHref;?>&idx=<?=$value['dealings_idx']?>&member_idx=<?=$value['dealings_writer_idx']?>">
									완료
								</a>
							<?php endif; ?>
						<?php endif; ?>
					</td>
					<td>
						<?php if($value['dealings_status']==3): ?>
							<?php if($value['dealings_type']=='구매'): ?>
								<a href="<?=$DealingsDetailViewHref;?>&idx=<?=$value['dealings_idx']?>&member_idx=<?=$value['dealings_writer_idx']?>">
									취소
								</a>
							<?php else: ?>
								<a href="<?=$DealingsDetailViewHref;?>&idx=<?=$value['dealings_idx']?>&member_idx=<?=$value['dealings_member_idx']?>">
									취소
								</a>
							<?php endif; ?>
						<?php endif; ?>
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