<?
/** @var array $constructData */
/** @var string $cKey */
/** @var array $base */

?>
<div class="controls">
	<div class="templ-parts"><?

		$single = count($constructData) > 1 ? '' : ' single';
		foreach ($constructData as $part)
		{
			?>
			<div class="part<?= $single ?>">
				<select class="input-xlarge num" name="c[<?= $cKey ?>][p][]" data-name="c[<?= $cKey ?>][p][index]"><?

					$selected = $part['KEY'] == 'text' ? ' selected' : '';
					?><option value="text"<?= $selected ?>>Простой текст</option><?

					$selected = $part['KEY'] == 'keyword' ? ' selected' : '';
					?><option value="keyword"<?= $selected ?>>Ключевая фраза целиком</option><?

					foreach ($base as $j => $col)
					{
						$char = chr(65 + $j);
						$val = 'col' . $j;
						$selected = $part['KEY'] == $val ? ' selected' : '';
						?><option value="<?= $val ?>"<?= $selected ?>>Столбец <?= $char ?> (<?=
						$col['WORDS'][0] ?>, <?= $col['WORDS'][1] ?>...)</option><?
					}

					?>
				</select>
				<i class="icon-remove"></i>
				<div class="part-cont"><?

					$selected = $part['KEY'] == 'text' ? ' selected' : '';
					$disabled = $part['KEY'] == 'text' ? '' : ' disabled';
					$value =  $part['KEY'] == 'text' ? $part['D'] : '';
					?>
					<div class="part-type type-text<?= $selected ?>">
						<input class="num" type="text" name="c[<?= $cKey ?>][text][]"
						       data-name="c[<?= $cKey ?>][text][index]" value="<?= $value ?>"<?= $disabled ?> />
					</div><?

					$selected = $part['KEY'] == 'keyword' ? ' selected' : '';
					?>
					<div class="part-type type-keyword<?= $selected ?>">
					</div><?

					foreach ($base as $j => $col)
					{
						$val = 'col' . $j;
						$selected = $part['KEY'] == $val ? ' selected' : '';
						$disabled = $part['KEY'] == $val ? '' : ' disabled';

						$words = $col['WORDS'];
						$nil = $col['REQ'] ? '' : '(нет)';
						array_unshift($words, $nil);
						?>
						<div class="part-type type-<?= $val ?><?= $selected ?>">
							<div class="tgl"><span>Задать соответствия</span></div>
							<div style="display:none;"><?
								foreach ($words as $w => $word)
								{
									if (!$word)
										continue;

									$default = $w ? $word : '';
									$value = $part['KEY'] == $val ? $part['D'][$w ? $word : ''] : $default;
									?><p>
									<span class="orig"><?= $word ?></span>
									<input class="input-small num" type="text" name="c[<?= $cKey ?>][col][][<?= $w ?>]"
									       value="<?= $value ?>"
									       data-name="c[<?= $cKey ?>][col][index][<?= $w ?>]"<?= $disabled?> /></p><?
								}
								?>
							</div>
						</div><?
					}
					?>
				</div>
			</div><?
		}

		?>
	</div>
	<div>
		<button class="btn add-part" type="button">Добавить</button>
	</div>
</div><?