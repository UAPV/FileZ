<script type="text/javascript">
    $(document).ready(function(){
        var htmlVal;
        var idUser;
        var newVal;

        $("table")
            .tablesorter({widthFixed: true, widgets: ['zebra']})
            .tablesorterPager({container: $("#pager")});

        $('.editQuota').bind('click',function(){
            idUser = $(this).parent().attr('id');
            htmlVal = $("#"+idUser+" .inputQuota").html();
            $("#"+idUser+" .inputQuota").wrapInner(document.createElement("input"));
            $("#"+idUser+" .inputQuota :input").val(htmlVal);
            $("#"+idUser+" .editQuota").css('display','none');
            $("#"+idUser+" .saveQuota").css('display', 'inline');
        })

//admin has changed the quota value, need to save it into the db
        $(".saveQuota").bind('click',function(){
            newVal = $("#"+idUser+" .inputQuota :input").val();

            if(newVal != htmlVal && newVal != '')
            {
                $.ajax({
                    url: '<?php echo url_for("/admin/users/editQuota")?>',
                    data: {idUser: idUser, quota: $("#"+idUser+" .inputQuota :input").val()},
                    type: "POST",
                    success:function(data){
                        if(data.error)
                        {
                            alert('Impossible de modifier le quota de l\'utilisateur car le quota entré à déjà été dépassé.');
                            $("#"+idUser+" .inputQuota").html(htmlVal);
                        }
                        else if(!data.success)
                            alert('Une erreur est survenue lors de la sauvegarde du nouveau quota');
                        else
                            $("#"+idUser+" .inputQuota").html(newVal);
                    }
                })
            }
            else
                $("#"+idUser+" .inputQuota").html(htmlVal);

            $("#"+idUser+" .inputQuota :input").remove();
            $("#"+idUser+" .editQuota").css('display','inline');
            $("#"+idUser+" .saveQuota").css('display', 'none');
        })
    });
</script>

<h2><?php echo __('Manage users') ?></h2>

<!-- TODO : find a jquery plugin to order and paginate the user list -->
<?php if ($EditUserRight): ?>
<p><a href="<?php echo url_for ('/admin/users/new') ?>" class="awesome"><?php echo __('Create a new user') ?></a></p>
<?php endif ?>
<table id="user_list" class="data tablesorter">
    <thead>
    <tr>
        <th><?php echo __('Name') ?></th>
        <th><?php echo __('Username') ?></th>
        <th><?php echo __('Role') ?></th>
        <th><?php echo __('File count') ?></th>
        <th><?php echo __('Disk usage') ?></th>
        <th><?php echo __('Max quota') ?></th>
        <!--<th><?php echo __('Expired files') ?></th>-->
        <?php if ($EditUserRight): ?><th><?php echo __('Actions') ?></th><?php endif ?>
    </tr>
    </thead>

    <tbody>
    <?php foreach ($users as $user_item): ?>
    <tr>
        <td><a href="<?php echo url_for ('/admin/users/'.$user_item->id) ?>"><?php echo h($user_item) ?></a></td>
        <td><a href="<?php echo url_for ('/admin/users/'.$user_item->id) ?>"><?php echo h($user_item->username) ?></a></td>
        <td><?php echo ($user_item->is_admin) ? __('admin') : '-' ?></td>
        <td><?php if (0<count ($user_item->getFiles ())): ?>
            <a href="<?php echo url_for ('/admin/users/'.$user_item->id) ?>">
                <?php echo count ($user_item->getFiles ()) ?>
            </a>
            <?php else: ?>
            0
            <?php endif ?>
        </td>
        <td><?php echo $diskUsage[$user_item->id] ?></td>
        <td id='<?php echo $user_item->id ?>'>
            <span class='inputQuota'><?php echo substr($user_item->getQuota(),0,-1) ?></span>G
            <span class='editQuota link'><?php echo __('Edit') ?></span>
            <span class='saveQuota link' style='display: none;'><?php echo __('Save') ?></span>
        </td>
        <!--<td><?php echo 'todo'/* TODO */ ?></td>-->
        <?php if ($EditUserRight): ?><td>
        <a href="<?php echo url_for ('/admin/users/'.$user_item->id.'/edit') ?>">
            <?php echo __('Edit') ?>
        </a>
        <?php if ( $fz_user->id != $user_item->id ) : // prevents self-deleting ?>
        <a onclick='javascript:return confirm (<?php echo json_encode( __r('Are you sure you want to delete the user "%displayname%" (%username%)', array ('displayname' => $user_item, 'username' => $user_item->username))) ?>)'
           href="<?php echo url_for ('/admin/users/'.$user_item->id.'/delete') ?>">
            <?php echo __('Delete') ?>
        </a>
        <?php endif ?>
    </td><?php endif /*EditUserRight*/?>
    </tr>
        <?php endforeach ?>
    </tbody>
</table>

<div id="pager" class="pager">
    <form>
        <img src="../resources/images/first.png" class="first"/>
        <img src="../resources/images/prev.png" class="prev"/>
        <input type="text" disabled="disabled" class="pagedisplay"/>
        <img src="../resources/images/next.png" class="next"/>
        <img src="../resources/images/last.png" class="last"/>
        <select class="pagesize">
            <option selected="selected" value="10">10</option>
            <option value="20">20</option>
            <option value="30">30</option>
            <option value="40">40</option>
        </select>
    </form>
</div>

