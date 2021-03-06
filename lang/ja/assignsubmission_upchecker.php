<?php
defined('MOODLE_INTERNAL') || die();

$string['configmaxbytes'] = '最大ファイルサイズ';
$string['countfiles'] = '{$a} ファイル';
$string['default'] = 'デフォルトで有効にする';
$string['default_help'] = '有効にした場合、すべての新しい課題において、この提出方法がデフォルトで有効にされます。';
$string['enabled'] = 'ファイル提出';
$string['enabled_help'] = '有効にした場合、学生は1つまたはそれ以上のファイルを提出物としてアップロードすることができます。';
$string['eventassessableuploaded'] = 'ファイルがアップロードされました。';
$string['file'] = 'ファイル提出';
$string['maxbytes'] = '最大ファイルサイズ';
$string['maxfilessubmission'] = '最大アップロードファイル数(upchecker)';
$string['maxfilessubmission_help'] = '有効にした場合、学生はこの数のファイルまで提出にアップロードすることができます。';
$string['maximumsubmissionsize'] = '最大提出サイズ(upchecker)';
$string['maximumsubmissionsize_help'] = '学生は最大このサイズまでファイルをアップロードすることができます。';
$string['numfilesforlog'] = 'ファイル数 : {$a} ファイル';
$string['pluginname'] = 'アップロードチェッカー';
$string['siteuploadlimit'] = 'サイトアップロード上限';
$string['submissionfilearea'] = 'アップロード済み提出ファイル';

// 未整理
$string['nofile'] = '(ファイルなし)';
$string['caution_help'] = 'アップロード時の注意点などを入力してください。';
$string['checkurl_help'] = '採点スクリプトのURLを入力してください。';
$string['dropbox'] = 'Dropbox';
$string['dropboxerror'] = 'Dropboxエラー: {$a}';
$string['example_help'] = '実行例を入力してください。';
$string['failedtocreatetmpfile'] = '一時ファイルが作成できません: {$a}';
$string['filepostname_help'] = '採点スクリプトに送信するファイルのパラメータ名（&lt;input type="file"/&gt;のname属性）を入力してください。その他のパラメータはその他のPOST変数名に入力してください。';
$string['hint_help'] = '解答のヒントを入力してください。';
$string['manualgrading'] = '手動採点';
$string['markingmethod_help'] = '';//file_get_contents(__DIR__.'/help/markingmethod.mdown');
$string['moodle'] = 'Moodle';
$string['overduecantsubmit'] = '締め切りを過ぎているため提出できません。';
$string['overduepenalized'] = '締め切りを過ぎているため最大評点は {$a} になります。';
$string['questionhtml'] = '問題ファイルURL';
$string['restparams_help'] = '';//file_get_contents(__DIR__.'/help/restparams.mdown');
$string['storagetype'] = '提出ファイルの保管先';
$string['text'] = 'テキスト';
$string['uploadfilename'] = '学生の提出ファイル名';
$string['uploadfilename_help'] = '';//file_get_contents(__DIR__.'/help/uploadfilename.mdown');
$string['xml'] = 'XML';
$string['xmlfeedbackelement_help'] = '採点方法にXML評価を指定した場合、受講者が解答後、ここに入力したタグの内容を表示します。';
$string['xmlgradeelement_help'] = '採点方法にXML評価を指定した場合は、サーバーから返されるXML中で得点が含まれるタグの名前を入力してください。';

$string['accept'] = '受け付ける';
$string['additionalparam'] = 'その他のPOST変数';
$string['addmoreanswerblanks'] = 'さらにサーバ返り値を追加する';
$string['afterlimit'] = '締切後も提出可能';
$string['answerno'] = 'サーバ返り値 $a';
$string['attemptaccepted'] = '<strong>送信済</strong>';
$string['attemptcondition'] = '対象受験';
$string['attemptinfoformat'] = '受験 $a->id (氏名: $a->name, 完了日時: $a->timefinish)';
$string['backtocourse'] = '受験結果に戻る';
$string['caution'] = '注意';
$string['cautionmessage'] = '注意書き';
$string['checkurl'] = 'チェックURL';
$string['continue'] = '続ける';
$string['continuefromcache'] = '途中までの評価結果がキャッシュされています。キャッシュの続きから取得しますか?';
$string['couldntgetresult'] = 'サーバーから評価結果の取得ができなかったため、評価の更新を中断しました。';
$string['downloadexecutable'] = '提出ファイルをダウンロード';
$string['example'] = '実行例';
$string['filepostname'] = 'チェックPOST変数名';
$string['filepostparam'] = 'ファイル提出用POST変数名';
$string['firstattempt'] = '最初';
$string['folderize'] = '1回ごとの受験別にフォルダを分ける';
$string['getgrades'] = '評価結果の取得';
$string['gettinggrade'] = '$a の評価結果を取得しています。';
$string['gradeis'] = '評点は $a でした。';
$string['hint'] = 'ヒント';
$string['isafterlimit'] = '締め切り経過';
$string['islimit'] = '締切を利用';
$string['lastattempt'] = '最後';
$string['lastattemptbeforeclosing'] = '締切前で最後';
$string['limitdatetime'] = '締切日時';
$string['limitpoint'] = '締切後の最高得点';
$string['markingmethod'] = '採点方法';
$string['markingmethodmanual'] = '手動評価';
$string['markingmethodtext'] = 'プレーンテキスト';
$string['markingmethodxml'] = 'XML評価';
$string['markingoption'] = '評価設定';
$string['materialfile'] = '提出ファイル';
$string['nolimit'] = '締め切りはありません。';
$string['notaccept'] = '受け付けない';
$string['notupcheckerquestion'] = 'アップロードチェッカー以外の問題です。';
$string['notuse'] = '利用しない';
$string['otherattempts'] = 'その他';
$string['outdated'] = '締め切りを過ぎているので提出できません。';
$string['outdatedandgradelimited'] = '締め切りを過ぎているので、最高得点は $a です。';
$string['pluginname'] = 'アップロードチェッカー';
$string['pluginname_help'] = 'アップロードチェッカー';
$string['pluginnameadding'] = 'アップロードチェッカーの追加';
$string['pluginnameediting'] = 'アップロードチェッカーの編集';
$string['pluginnamesummary'] = 'アップロードチェッカー';
$string['questionisclosed'] = 'この問題は締め切られています。';
$string['reallyupdategrades'] = '学生の提出したすべてのファイルをチェックサーバーに送信して評価を更新します。よろしいですか?';
$string['reallyupdateupcheckergrades'] = 'この小テスト中のすべてのアップロードチェッカーの受験結果を更新してよろしいですか?';
$string['regrade'] = '再評定';
$string['regradedone'] = '再評価が完了しました。';
$string['regrading'] = '評価結果の更新';
$string['restart'] = '再実行';
$string['restparams'] = 'その他のPOST変数名';
$string['upchecker'] = 'アップロードチェッカー';
$string['updatedattempt'] = '$a の評価結果を更新しました。';
$string['updategrades'] = '評価結果の更新';
$string['updategrades'] = '評価結果を更新する';
$string['uploadheader'] = '提出ファイルの詳細';
$string['uploadoption'] = 'アップロード設定';
$string['use'] = '利用する';
$string['xmlanswerelement'] = '正解XMLタグ';
$string['xmlfeedbackelement'] = 'フィードバックXMLタグ';
$string['xmlgradeelement'] = '得点XMLタグ';
