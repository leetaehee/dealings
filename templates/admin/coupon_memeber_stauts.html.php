<p>
	<h3>[<?=TITLE_COUPON_ISSUE_MEMBER?>]</h3>
</p>
<table class="table dealings-table-width">
	<colgroup>
		<col style="width: 7%;">
		<col style="width: 11%;">
		<col style="width: 15%;">
		<col style="width: 20%;">
		<col style="width: 16%;">
		<col style="width: 10%;">
		<col style="width: 11%;">
		<col style="width: 11%;">
	</colgroup>
	<thead>
		<tr>
			<th>순번</th>
			<th>이름</th>
			<th>ID</th>
			<th>이메일</th>
			<th>연락처</th>
			<th>성별</th>
			<th>목록</th>
			<th>등록</th>
		</tr>
	</thead>
	<tbody>
		<?php if($rocordCount > 0): ?>
			<?php foreach($memberList as $key => $value): ?>
				<tr>
					<td><?=$key+1?></td>
					<td><?=setDecrypt($value['name'])?></td>
					<td><?=$value['id']?></td>
					<td><?=setDecrypt($value['email'])?></td>
					<td><?=setDecrypt($value['phone'])?></td>
					<td><?=$value['sex_name']?></td>
					<td>
						<a href="<?=$couponProvideStatusURL?>?idx=<?=$value['idx']?>">
							관리하기
						</a>
					</td>
					<td>
						<a href="<?=$couponProvideURL?>?idx=<?=$value['idx']?>">
							지급하기
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