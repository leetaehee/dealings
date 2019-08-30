<!--
<form id="admin-list" method="post" action="<?=actionUrl?>">
	<label for="search-name">이름</label>
	<input type="text" id="search-name" name="search_name" value="<?=$search_name;?>">
	<input type="submit" value="검색">
</form>
-->

<table class="table admin-table-width">
	<colgroup>
		<col style="width: 5%;">
		<col style="width: 13%;">
		<col style="width: 13%;">
		<col style="width: 4%;">
		<col style="width: 9%;">
		<col style="width: 20%;">
		<col style="width: 9%;">
		<col style="width: 9%;">
		<col style="width: 9%;">
		<col style="width: 9%;">
	</colgroup>
	<thead>
		<tr>
			<th>순번</th>
			<th>이름(아이디)</th>
			<th>생년월일</th>
			<th>성별</th>
			<th>레벨</th>
			<th>연락처 정보</th>
			<th>가입일<br>(메일승인일자)</th>
			<th>탈퇴일</th>
			<th>관리자설정</th>
			<th>강제탈퇴설정</th>
		</tr>
	</thead>
	<tbody>
		<?php if(count($members) > 0): ?>
			<?php foreach($members as $index => $mb): ?>
				<tr>
					<td><?=$index+1?></td>
					<td><?=setDecrypt($mb['name'])?><br>(<?=$mb['id']?>)</td>
					<td><?=setDecrypt($mb['birth'])?></td>
					<td><?=$mb['sex_name']?></td>
					<td>
						<?php if(!empty($mb['grade_name'])): ?>
							<?=$mb['grade_name']?>
						<?php else: ?>
							<?=$mb['nm_grade_name']?>
						<?php endif;?>
					</td>
					<td>
						<?=setDecrypt($mb['phone'])?><br>
						(<?=setDecrypt($mb['email'])?>)
					</td>
					<td>
						<?=$mb['join_date']?>
						<?php if(!empty($mb['join_approval_date'])):?>
							<br>(<?=$mb['join_approval_date'];?>)
						<?php endif; ?>
					</td>
					<td>
						<?php if(!empty($mb['withdraw_date'])): ?>
							<?=$mb['withdraw_date']?>
						<?php endif; ?>
					</td>
					<td>
						<?php if($_SESSION['admin_grade']==2): ?>
							<?php if($_SESSION['id']!=$mb['id']): ?>
								<a href="<?=$settingManagerUrl?>&value=<?=$choiceArray[$mb['adm']]['integer']?>&idx=<?=$mb['idx']?>">
									<?=$choiceArray[$mb['adm']]['text']?>
								</a>
							<?php endif; ?>
						<?php else: ?>
							권한없음
						<?php endif; ?>
					</td>
					<td>
						<?php if($_SESSION['admin_grade']==2): ?>
							<?php if($_SESSION['id']!=$mb['id']): ?>
								<a href="<?=$settingForcedEvictionUrl?>&value=<?=$choiceArray[$mb['fe']]['value']?>&idx=<?=$mb['idx']?>">
									<?=$choiceArray[$mb['fe']]['text']?>
								</a>
							<?php endif; ?>
						<?php else: ?>
							권한없음
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach;?>
		<?php else: ?>
			<tr>
				<td colspan="10" class="empty-tr-colspan">등록된 회원이 없습니다.<br>Empty Data.</td>
			</tr>
		<?php endif; ?>
	</tbody>
</table>