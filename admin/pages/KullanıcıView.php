<?php
namespace App\Admin;

use App\UsersControler;

$userControler = new UsersControler();
$users= $userControler->getUsers();
?>
<div class="tab-pane fade" id="kullanıcılarTabContent" role="tabpanel" aria-labelledby="kullanıcılar-tab">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-sm-flex justify-content-between align-items-start">
                    <div>
                        <h4 class="card-title card-tit-dash">Kullanıcı Yönetimi</h4>
                        <h5 class="card-subtitle card-subtitle-dash">Kullanıcı Yönetimi</h5>
                    </div>
                    <button type="button" class="btn btn-primary btn-icon-text">
                        <i class="ti-plus btn-icon-prepend"></i>
                        Yeni ekle
                    </button>
                </div>
                <div>
                    <div class="table-responsive pt-3">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>
                                    #
                                </th>
                                <th>
                                    Kullanıcı Adı
                                </th>
                                <th>
                                    Mail Adresi
                                </th>
                                <th>
                                    Adı
                                </th>
                                <th>
                                    Soyadı
                                </th>
                                <th>
                                    Profil Fotoğrafı
                                </th>
                                <th>
                                    Kayıt Tarihi
                                </th>

                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $sayac=1;
                            foreach ($users as $user){
                                ?>
                                <tr>
                                    <td>
                                        <?= $sayac?>
                                    </td>
                                    <td>
                                        <?= $user->userName?>
                                    </td>
                                    <td>
                                        <?= $user->mail?>
                                    </td>
                                    <td>
                                        <?= $user->name?>
                                    </td>
                                    <td>
                                        <?= $user->lastName?>
                                    </td>
                                    <td>
                                        <?= $user->profilPicture?>
                                    </td>
                                    <td>
                                        <?= $user->createdDate?>
                                    </td>
                                </tr>
                                <?php
                                $sayac++;
                            }
                            ?>


                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>