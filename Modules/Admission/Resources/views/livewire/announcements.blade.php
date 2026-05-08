<div>
    {{-- Breadcrumb --}}
    <x-admission::breadcrumb />

    <section id="announcements">
        <div class="container">
            <div class="grid">
                <div class="col-12">
                    <div class="card card-announcements">
                        <div class="card__header">
                            <h1 class="title">
                                {{ __('admission::announcement.main') }}
                            </h1>
                            <p class="description">
                                {{ __('admission::announcement.header_description') }}
                            </p>
                        </div>
                        <div class="line-bold"></div>
                        <div class="card__body">
                            <div class="announcement-list">
                                @php
                                    // FIXME: data belum dinamis dan pagination belum selesai
                                @endphp
                                <x-admission::news :data="$news" />
{{--                                <?php if (!empty($a_data)) : ?>--}}
{{--                                    <div class="grid">--}}
{{--                                        <?php foreach ($a_data as $pengumuman) : ?>--}}
{{--                                        <?php $thumb = Route::getImageURL(mPengumuman::IMGDIR, $pengumuman['idpengumuman'], false, null, true); ?>--}}
{{--                                        <div class="col-md-4 col-lg-3 col-sm-6 col-12">--}}
{{--                                            <a href="<?= Route::getNavAddress('detail-pengumuman/' . mPengumuman::getIDLink($pengumuman)) ?>">--}}
{{--                                                <div class="card-berita">--}}
{{--                                                    <div class="thumbnail">--}}
{{--                                                            <?php if ($thumb) : ?>--}}
{{--                                                        <div class="thumbnail">--}}
{{--                                                            <img loading="lazy" src="<?= $thumb ?>" alt="pengumuman-img">--}}
{{--                                                        </div>--}}
{{--                                                        <?php else : ?>--}}
{{--                                                        <div class="thumbnail">--}}
{{--                                                            <div class="empty">--}}
{{--                                                                <img loading="lazy" src="<?= Auth::getSettingSIM('university_logo') ?>" alt="pengumuman-img" style="object-fit: contain;">--}}
{{--                                                            </div>--}}
{{--                                                        </div>--}}
{{--                                                        <?php endif; ?>--}}
{{--                                                    </div>--}}
{{--                                                    <div class="right-content">--}}
{{--                                                        <p class="date"><?= CStr::formatDateInd($pengumuman['tglpengumuman'], true, false, '-', false) ?></p>--}}
{{--                                                        <h1 class="title-berita"><?= $pengumuman['judulpengumuman'] ?></h1>--}}
{{--                                                            <?php if ($pengumuman['jenis'] == mPengumuman::INFORMASI) : ?>--}}
{{--                                                        <span class="badge badge--info">Informasi</span>--}}
{{--                                                        <?php elseif ($pengumuman['jenis'] == mPengumuman::PENGUMUMAN) : ?>--}}
{{--                                                        <span class="badge badge--warning">Pengumuman</span>--}}
{{--                                                        <?php endif; ?>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                            </a>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                    <?php endforeach; ?>--}}
{{--                                <?php else : ?>--}}
{{--                                    <x-core::handler title="Belum Ada Informasi Dan Pengumuman"--}}
{{--                                                     subtitle="Belum ada informasi dan pengumuman terbaru" />--}}
{{--                                <?php endif; ?>--}}
                            </div>

                            @if(!empty($news))
                                <x-core::table.navigation :data="$news" :hideOption="true" />
                            @endif
{{--                            <?php if (!empty($a_data)) : ?>--}}
{{--                                <?php--}}
{{--                                $a = floor($p_page['count'] / $p_page['limit']);--}}
{{--                                ?>--}}
{{--                                <div class="pagination-container">--}}
{{--                                    <div class="pagination-detail">Menampilkan <strong><?= $a >= $p_page['page'] ? $p_page['page'] * $p_page['limit'] : $p_page['count'];  ?></strong> dari <strong><?= $p_page['count'] ?></strong>--}}
{{--                                        Informasi dan Pengumuman--}}
{{--                                    </div>--}}
{{--                                    <form name="pageform" id="pageform" method="post">--}}
{{--                                        <ul class="pagination">--}}
{{--                                                <?php if ($p_page['firstdata'] > 0) { ?>--}}
{{--                                            <li class="prev<?php if ($p_page['isfirst']) { ?> disabled<?php } ?>">--}}
{{--                                                <a href="<?php echo $p_page['isfirst'] ? '#' : 'javascript:goPage(1)' ?>" data-toggle="tooltip" title="<?= Language::getValue('Halaman Pertama') ?>" id="btn-first">--}}
{{--                                                        <span class="material-icons-round">--}}
{{--                                                            keyboard_double_arrow_left--}}
{{--                                                        </span>--}}
{{--                                                </a>--}}
{{--                                            </li>--}}
{{--                                            <?php } ?>--}}
{{--                                                <?php if (!is_null($p_page['count'])) { ?>--}}
{{--                                            <li class="prev<?php if ($p_page['isfirst']) { ?> disabled<?php } ?>">--}}
{{--                                                <a href="<?php echo $p_page['isfirst'] ? '#' : 'javascript:goPage(' . ($p_page['page'] - 1) . ')' ?>" data-toggle="tooltip" title="<?= Language::getValue('Halaman Sebelumnya') ?>" id="btn-previous">--}}
{{--                                                        <span class="material-icons-round">--}}
{{--                                                            chevron_left--}}
{{--                                                        </span>--}}
{{--                                                </a>--}}
{{--                                            </li>--}}
{{--                                                <?php for ($i = $p_page['firstnav']; $i < $p_page['page']; $i++) { ?>--}}
{{--                                            <li>--}}
{{--                                                <a href="javascript:goPage(<?php echo $i ?>)" data-toggle="tooltip" title="<?= Language::getValue('Halaman') ?> <?php echo $i ?>" id="hal-<?= $i ?>"><?php echo $i ?></a>--}}
{{--                                            </li>--}}
{{--                                            <?php } ?>--}}
{{--                                            <li class="active"><a href="#" id="hal-<?= $p_page['page'] ?>"><?php echo $p_page['page'] ?></a></li>--}}
{{--                                                <?php for ($i = $p_page['page'] + 1; $i <= $p_page['lastnav']; $i++) { ?>--}}
{{--                                            <li><a href="javascript:goPage(<?php echo $i ?>)" data-toggle="tooltip" title="<?= Language::getValue('Halaman') ?> <?php echo $i ?>" id="hal-<?= $i ?>"><?php echo $i ?></a></li>--}}
{{--                                            <?php } ?>--}}
{{--                                            <li class="next<?php if ($p_page['islast']) { ?> disabled<?php } ?>">--}}
{{--                                                <a href="<?php echo $p_page['islast'] ? '#' : 'javascript:goPage(' . ($p_page['page'] + 1) . ')' ?>" data-toggle="tooltip" title="<?= Language::getValue('Halaman Selanjutnya') ?>" id="btn-next">--}}
{{--                                                        <span class="material-icons-round">--}}
{{--                                                            chevron_right--}}
{{--                                                        </span>--}}
{{--                                                </a>--}}
{{--                                            </li>--}}
{{--                                            <?php } else { ?>--}}
{{--                                            <li class="prev<?php if ($p_page['isfirst']) { ?> disabled<?php } ?>">--}}
{{--                                                <a href="<?php echo $p_page['isfirst'] ? '#' : 'javascript:goPage(' . ($p_page['page'] - 1) . ')' ?>" data-toggle="tooltip" title="<?= Language::getValue('Halaman Sebelumnya') ?>" id="btn-previous">--}}
{{--                                                    Sebelumnya--}}
{{--                                                    <span class="material-icons-round">--}}
{{--                                                            chevron_left--}}
{{--                                                        </span>--}}
{{--                                                </a>--}}
{{--                                            </li>--}}
{{--                                            <li class="next<?php if ($p_page['islast']) { ?> disabled<?php } ?>">--}}
{{--                                                <a href="<?php echo $p_page['islast'] ? '#' : 'javascript:goPage(' . ($p_page['page'] + 1) . ')' ?>" data-toggle="tooltip" title="<?= Language::getValue('Halaman Selanjutnya') ?>" id="btn-next">--}}
{{--                                                    Selanjutnya--}}
{{--                                                    <span class="material-icons-round">--}}
{{--                                                            chevron_right--}}
{{--                                                        </span>--}}
{{--                                                </a>--}}
{{--                                            </li>--}}
{{--                                            <?php } ?>--}}
{{--                                                <?php if ($p_page['lastpage'] > 0) { ?>--}}
{{--                                            <li class="next<?php if ($p_page['islast']) { ?> disabled<?php } ?>">--}}
{{--                                                <a href="<?php echo $p_page['islast'] ? '#' : 'javascript:goPage(' . $p_page['lastpage'] . ')' ?>" data-toggle="tooltip" title="<?= Language::getValue('Halaman Terakhir') ?>" id="btn-end">--}}
{{--                                                        <span class="material-icons-round">--}}
{{--                                                            keyboard_double_arrow_right--}}
{{--                                                        </span>--}}
{{--                                                </a>--}}
{{--                                            </li>--}}
{{--                                            <?php } ?>--}}
{{--                                        </ul>--}}
{{--                                        <input type="hidden" name="page" id="page">--}}
{{--                                        <input type="hidden" name="act" id="act">--}}
{{--                                    </form>--}}
{{--                                </div>--}}
{{--                            <?php endif; ?>--}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
