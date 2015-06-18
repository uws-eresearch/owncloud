<crate>
<name><?php p($_['crate_name']) ?></name>
<date><?php p($_['created_date_formatted'])?></date>
<description><?php p($_['description'])?></description>
  <?php if($_['creators']){?>
    <creators>
    <?php foreach ($_['creators'] as $creator) {
        print_unescaped('<creator>');
        print_unescaped('<name>');
        if($creator['overrides']['name']){
            p($creator['overrides']['name']);
        } else {
            p($creator['name']);
        }
        print_unescaped('</name>');
        print_unescaped('<email>');
        if($creator['overrides']['email']) {
            p($creator['overrides']['email']);
        } else {
            p($creator['email']);
        }
        print_unescaped('</email>');
        print_unescaped('</creator>');
    }?>
    </creators>
 <?php } ?>
<submitter>
    <name><?php p($_['submitter']) ?></name>
</submitter>
</crate>