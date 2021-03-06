<?php
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2012  DBselller Servicos de Informatica             
 *                            www.dbseller.com.br                     
 *                         e-cidade@dbseller.com.br                   
 *                                                                    
 *  Este programa e software livre; voce pode redistribui-lo e/ou     
 *  modifica-lo sob os termos da Licenca Publica Geral GNU, conforme  
 *  publicada pela Free Software Foundation; tanto a versao 2 da      
 *  Licenca como (a seu criterio) qualquer versao mais nova.          
 *                                                                    
 *  Este programa e distribuido na expectativa de ser util, mas SEM   
 *  QUALQUER GARANTIA; sem mesmo a garantia implicita de              
 *  COMERCIALIZACAO ou de ADEQUACAO A QUALQUER PROPOSITO EM           
 *  PARTICULAR. Consulte a Licenca Publica Geral GNU para obter mais  
 *  detalhes.                                                         
 *                                                                    
 *  Voce deve ter recebido uma copia da Licenca Publica Geral GNU     
 *  junto com este programa; se nao, escreva para a Free Software     
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA          
 *  02111-1307, USA.                                                  
 *  
 *  Copia da licenca no diretorio licenca/licenca_en.txt 
 *                                licenca/licenca_pt.txt 
 */

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta_plugin.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("classes/db_empagegera_classe.php"));
require_once(modification("classes/db_empageconfgera_classe.php"));
require_once(modification("classes/db_empagetipo_classe.php"));
require_once(modification("classes/db_empagedadosret_classe.php"));
$clempagegera     = new cl_empagegera;
$clempageconfgera = new cl_empageconfgera;
$clempagetipo     = new cl_empagetipo;
$clempagedadosret = new cl_empagedadosret;
$clrotulo         = new rotulocampo;
$clempagegera->rotulo->label();
$clempagetipo->rotulo->label();
$clempagedadosret->rotulo->label();

db_postmemory($HTTP_POST_VARS);

$action = "Confirmar ";
$formul = "emp4_empageretornoconf001.php?lCancelado=0";
$TorF = "true";
if(isset($canc)){
  $action = "Cancelar ";
  $formul = "emp4_empageretornocanc001.php?lCancelado=0";
  $TorF = "false";
}
?>
<html>
<head>
<title>DBSeller Inform&aacute;tica Ltda - P&aacute;gina Inicial</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Expires" CONTENT="0">
<script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
<link href="estilos.css" rel="stylesheet" type="text/css">
</head>
  <body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onLoad="document.form1.e75_codret.focus();" bgcolor="#cccccc">
    <table width="790" border="0" cellpadding="0" cellspacing="0" bgcolor="#5786B2">
      <tr>
	<td width="360" height="18">&nbsp;</td>
	<td width="263">&nbsp;</td>
	<td width="25">&nbsp;</td>
	<td width="140">&nbsp;</td>
      </tr>
    </table>
<center>
<form name="form1" method="post">
<table border='0'>
  <tr height="20px">
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr> 
    <td  align="left" nowrap title="<?=$Te87_codgera?>"> <? db_ancora(@$Le75_codret,"js_pesquisa_gera(true);",1);?>  </td>
    <td align="left" nowrap>
  <?  
   db_input("e75_codret",8,$Ie75_codret,true,"text",4,"onchange='js_pesquisa_gera(false);'"); 
   db_input("e87_codgera",8,$Ie87_codgera,true,"text",3); 
   db_input("e87_descgera",40,$Ie87_descgera,true,"text",3);
  ?>
    </td>
  </tr>
  <?
  $db_passapar = "true";
  if(isset($e75_codret)){
    echo "
    <tr> 
      <td  align='left' nowrap title='Conta pagadora'>
    ";
    db_ancora("<strong>Conta pagadora:</strong>","",3);
    echo "
      <td align='left' nowrap>
    ";
    $result_empagetipo = $clempageconfgera->sql_record($clempageconfgera->sql_query_inf(null,@$e87_codgera,"distinct e83_codtipo,e83_descr"));
    if($clempagetipo->numrows == 0){
      $db_passapar = "false";
    }
    
    db_selectrecord("e83_codtipo",$result_empagetipo,true,1,"","","","0");
    echo "
      </td>
    </tr>
    ";
  }
  ?>  
  <tr>
    <td colspan="2" align="center"><br>
      <input name="act" type="button" <?=("onclick='js_geraact($db_passapar);'")?>  value="Mostrar retorno">
      <input name="pes" type="button" onclick='js_OpenJanelaIframe("CurrentWindow.corpo","db_iframe_empageret","func_pluginGeracaoArquivoOBNArquivosProcessados.php?funcao_js=parent.js_mostragera1|e87_codgera|e87_descgera","Pesquisa",true);'  value="Pesquisar arquivos">
    </td>
  </tr>
</table>
</form>
</center>
<? db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));?>
<script>
//--------------------------------
function js_geraact(x){
  if(document.form1.e75_codret && document.form1.e75_codret.value!=""){
    qry = "retornoarq="+document.form1.e75_codret.value;
    if(document.form1.e83_codtipo.value!="0"){
      qry+= "&contapaga="+document.form1.e83_codtipo.value;
    }
    qry+= "&retornomn=<?=@$TorF?>";
    location.href = "<?=@$formul?>&"+qry;
  }else if(!document.form1.e75_codret){
    alert("Informe o c�digo de um arquivo j� processado.");
  }else{
    alert("Informe o c�digo do retorno v�lido para <?=@$action?> movimentos.");
  }
}
function js_pesquisa_gera(mostra){
  if(mostra==true){
    js_OpenJanelaIframe('CurrentWindow.corpo','db_iframe_empageret','func_pluginGeracaoArquivoOBNArquivosProcessados.php?lfiltroMovimento=true&funcao_js=parent.js_mostragera1|e75_codret|e87_codgera|e87_descgera','Pesquisa',true);
  }else{
     if(document.form1.e75_codret.value != ''){ 
        js_OpenJanelaIframe('CurrentWindow.corpo','db_iframe_empageret','func_pluginGeracaoArquivoOBNArquivosProcessados.php?pesquisa_chave='+document.form1.e75_codret.value+'&funcao_js=parent.js_mostragera&lfiltroMovimento=true','Pesquisa',false);
     }else{
       document.form1.e87_descgera.value = ''; 
     }
  }
}
function js_mostragera(chave1, chave2, erro){
  if(erro==true){ 
    document.form1.e75_codret.focus(); 
    document.form1.e75_codret.value = ''; 
  }
  document.form1.e87_codgera.value = chave1; 
  document.form1.e87_descgera.value = chave2; 
  document.form1.submit();
}
function js_mostragera1(chave1,chave2,chave3){
  document.form1.e75_codret.value = chave1;
  document.form1.e87_codgera.value = chave2;
  document.form1.e87_descgera.value = chave3;
  db_iframe_empageret.hide();
  document.form1.submit();
}
//--------------------------------
</script>
</body>
</html>