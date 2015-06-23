<crate>
<name><?php p($_['crate_name']) ?></name>
<date><?php p($_['created_date'])?></date>
<locaation><?php p($_['location'])?></locaation>
<description><?php p($_['description'])?></description>
  <?php if($_['creators']){?>
    <creators>
    <?php foreach ($_['creators'] as $creator) { ?>
        <creator>
            <?php
            print_unescaped('<name>');
            if($creator['overrides']['name']){
                p($creator['overrides']['name']);
            } else {
                p($creator['name']);
            }
            print_unescaped('</name>');
            ?>
            <?php
            print_unescaped('<email>');
            if($creator['overrides']['email']) {
                p($creator['overrides']['email']);
            } else {
                p($creator['email']);
            }
            print_unescaped('</email>');
            ?>
        </creator>
    <?php } ?>
    </creators>
 <?php } ?>
<submitter>
    <name></name>
    <email></email>
</submitter>
</crate>