
<?php
if (isset($_POST["req"])) {
  require "2-lib-ticket.php";
  switch ($_POST["req"]) {
    // (A) LIST TICKETS
    case "getAll":
      $tickets = $_TKT->getAll($_ADM ? null : $_USR["id"]);
      if (count($tickets)>0) { foreach ($tickets as $t) { ?>
      <div class="row">
        <div class="grow">
          <span class="tStat s<?=$t["ticket_status"]?>"><?=TKT_STAT[$t["ticket_status"]]?></span>
          <div class="tUser"><?=$t["user_name"]?> &#x2022; <?=$t["ticket_date"]?></div>
          <div class="tTxt"><?=$t["ticket_subject"]?></div>
        </div>
        <div class="tShow" onclick="tix.show(<?=$t["ticket_id"]?>)">&#x27A4;</div>
      </div>
      <?php }} else { echo "No tickets found."; }
      break;

    // (B) ADD/UPDATE/VIEW TICKET
    case "show":
      // (B1) SET "PAGE MODE"
      // 1 - ADD | 2 - UPDATE | 3 - VIEW
      if (is_numeric($_POST["tid"])) { $mode = $_ADM ? 2 : 3 ; }
      else { $mode = 1; }

      // (B2) UPDATE OR VIEW - GET TICKET
      if ($mode == 2 || $mode == 3) {
        $t = $_TKT->get($_POST["tid"]);
        $h = $_TKT->getHistory($_POST["tid"]);
        if (!is_array($t)) { exit("Invalid Ticket"); }
        if (!$_ADM && $t["user_id"]!=$_USR["id"]) { exit("Invalid Ticket"); }
      }

      // (B3) TICKET FORM ?>
      <form class="section" onsubmit="return tix.save()">
        <h2><?=$mode==1?"ADD":($mode==2?"UPDATE":"VIEW")?> TICKET</h2>
        <input type="hidden" id="tID" value="<?=isset($t)?$t["ticket_id"]:""?>">

        <?php if ($mode!=1) { ?>
        <label>Created At</label>
        <input type="text" readonly value="<?=$t["ticket_date"]?>">

        <label>Created By</label>
        <input type="text" readonly value="<?=$t["user_name"]?>">
        <?php } ?>

        <label>Subject</label>
        <input type="text" id="tSubject" required<?=$mode==1?"":" readonly"?>
               value="<?=isset($t)?$t["ticket_subject"]:""?>">

        <label>Details</label>
        <textarea id="tTxt" required<?=$mode==1?"":" readonly"?>><?=isset($t)?$t["ticket_txt"]:""?></textarea>

        <?php if ($mode==2 || $mode==3) { ?>
        <label>Status</label>
        <select id="tStat"<?=$mode==3?" disabled":""?>><?php
        foreach (TKT_STAT as $k=>$v) {
          printf("<option value='%u'%s>%s</option>",
            $k, $k==$t["ticket_status"]?" selected":"", $v
          );
        }
        ?></select>
        <?php } ?>

        <?php if ($mode==2) { ?>
        <label>Update Notes (If Any)</label>
        <input type="text" id="tNote">
        <?php } ?>

        <input type="button" value="Back" onclick="tix.toggle('A')">
        <?php if ($mode!=3) { ?>
        <input type="submit" value="Save">
        <?php } ?>
      </form>

      <?php
      // (B4) TICKET HISTORY 
      if ($mode==2 && count($h)>0) { ?>
      <div id="tHistory" class="section">
        <h2>TICKET HISTORY</h2>
        <?php foreach ($h as $i) { ?>
        <div class="hRow">
          <div class="hDate">[<?=$i["history_date"]?>] <?=TKT_STAT[$i["history_status"]]?></div>
          <div class="hNote"><?=$i["history_note"]?></div>
        </div>
        <?php } ?>
      </div>
      <?php }
      break;

    // (C) SAVE TICKET
    case "save":
      if (isset($_POST["tid"])) {
        $_TKT->update($_POST["status"], $_POST["tid"], $_POST["note"], $_USR["id"]);
      } else {
        $_TKT->add($_POST["subject"], $_POST["txt"], $_USR["id"]);
      }
      echo "OK";
      break;
}}