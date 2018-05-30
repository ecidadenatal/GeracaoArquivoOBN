<?php
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2014  DBselller Servicos de Informatica             
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
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("libs/db_conecta_plugin.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/JSON.php"));
require_once(modification("libs/exceptions/BusinessException.php"));
require_once(modification("libs/exceptions/DBException.php"));
require_once(modification("libs/exceptions/ParameterException.php"));
require_once(modification("dbforms/db_funcoes.php"));

$oJson                  = new services_json();
$oParam                 = $oJson->decode(str_replace("\\","",$_POST["json"]));
$oRetorno               = new stdClass();
$oRetorno->iStatus      = 1;
$oRetorno->sMessage     = '';
$aDadosRetorno          = array();

try {

  switch ($oParam->exec) {

  	case 'getDetalhes' :

  		$iMovimento = $oParam->iMovimento;
  		$aDetalhes  = array();
      $aDetalhesPagamento = array();
  		$oDaoEmpAgeMovDetalheTransmissao = db_utils::getDao("empagemovdetalhetransmissao");
      $oDaoEmpAgeMovPagamento          = db_utils::getDao("empagemovpagamento");

  		$sSqlDetalhes = $oDaoEmpAgeMovDetalheTransmissao->sql_query_file (null, "*", null, "e74_empagemov = {$iMovimento}");
      $sSqlDetalhesPagamento = $oDaoEmpAgeMovPagamento->sql_query_empagemov ("*", "", "empagemov = {$iMovimento}");

  		$rsDetalhes = $oDaoEmpAgeMovDetalheTransmissao->sql_record($sSqlDetalhes);
      $rsDetalhesPagamento = $oDaoEmpAgeMovPagamento->sql_record($sSqlDetalhesPagamento);

  		if ($oDaoEmpAgeMovDetalheTransmissao->numrows > 0) {

  		  for ($iDetahes = 0; $iDetahes < $oDaoEmpAgeMovDetalheTransmissao->numrows; $iDetahes++) {

  		  	$oValorDetalhe = db_utils::fieldsMemory($rsDetalhes, $iDetahes);
  		  	$oDadosDetalhe = new stdClass();
  		  	$sFatura       = "Fatura";
  		  	if ($oValorDetalhe->e74_tipofatura == 2) {
  		  		$sFatura = "Convênio";
  		  	}
  		  	$oDadosDetalhe->e74_sequencial     = $oValorDetalhe->e74_sequencial    ;
  		  	$oDadosDetalhe->e74_empagemov      = $oValorDetalhe->e74_empagemov     ;
  		  	$oDadosDetalhe->e74_codigodebarra  = $oValorDetalhe->e74_codigodebarra ;
  		  	$oDadosDetalhe->e74_valornominal   = $oValorDetalhe->e74_valornominal  ;
  		  	$oDadosDetalhe->e74_datavencimento = $oValorDetalhe->e74_datavencimento;
  		  	$oDadosDetalhe->e74_valorjuros     = $oValorDetalhe->e74_valorjuros    ;
  		  	$oDadosDetalhe->e74_valordesconto  = $oValorDetalhe->e74_valordesconto ;
  		  	$oDadosDetalhe->sFatura            = urlencode($sFatura);
  		  	$oDadosDetalhe->e74_linhadigitavel = $oValorDetalhe->e74_linhadigitavel;
  		  	$aDetalhes[] = $oDadosDetalhe;
  		  }
  		}

      if ($oDaoEmpAgeMovPagamento->numrows > 0) { 

        for ($iDetalhesPag = 0; $iDetalhesPag < $oDaoEmpAgeMovPagamento->numrows; $iDetalhesPag++) {

          $oValorDetalhePagamento = db_utils::fieldsMemory($rsDetalhesPagamento, $iDetalhesPag);
          $oDadosDetalhePagamento = new stdClass();
          
          $sFatura       = "GPS";
          if ($oValorDetalhePagamento->tipopagamento == 2) {
            $sFatura = "DARF";
          } else if ($oValorDetalhePagamento->tipopagamento == 3) {
            $sFatura = "DARF Simples";
          }

          $oDadosDetalhePagamento->sFatura            = urlencode($sFatura);
          $oDadosDetalhePagamento->tipopagamento        = $oValorDetalhePagamento->tipopagamento;
          $oDadosDetalhePagamento->codreceita           = $oValorDetalhePagamento->codreceita;
          $oDadosDetalhePagamento->codidentificacao     = $oValorDetalhePagamento->codidentificacao;
          $oDadosDetalhePagamento->periodoapuracao      = $oValorDetalhePagamento->periodoapuracao;
          $oDadosDetalhePagamento->datavencimento       = $oValorDetalhePagamento->datavencimento;
          $oDadosDetalhePagamento->mesanocompetencia    = $oValorDetalhePagamento->mesanocompetencia;
          $oDadosDetalhePagamento->numreferencia        = $oValorDetalhePagamento->numreferencia;
          $oDadosDetalhePagamento->valorINSS            = $oValorDetalhePagamento->valorinss;
          $oDadosDetalhePagamento->valoroutras          = $oValorDetalhePagamento->valoroutras;
          $oDadosDetalhePagamento->atualizacaomonetaria = $oValorDetalhePagamento->atualizacaomonetaria;
          $oDadosDetalhePagamento->valorreceitabruta    = $oValorDetalhePagamento->valorreceitabruta;
          $oDadosDetalhePagamento->percentualreceita    = $oValorDetalhePagamento->percentualreceita;
          $oDadosDetalhePagamento->valorprincipal       = $oValorDetalhePagamento->valorprincipal;
          $oDadosDetalhePagamento->valormulta           = $oValorDetalhePagamento->valormulta;
          $oDadosDetalhePagamento->jurosencargos        = $oValorDetalhePagamento->jurosencargos;

          $aDetalhesPagamento[] = $oDadosDetalhePagamento;
        }
      }
  		$oRetorno->aDados = $aDetalhes;
      $oRetorno->aDadosPagamento = $aDetalhesPagamento;

  	break;

  	case "getTipoTransmissao" :

  		$aTiposTransmissao            = array();
  		$iCodigoMovimento             = $oParam->iMovimento;
  		$oDaoEmpAgeTipoTransmissao    = db_utils::getDao("empagetipotransmissao");
  		$oDaoEmpAgeMovTipoTransmissao = db_utils::getDao("empagemovtipotransmissao");

  		// verificamos o tipo atual cadastrado para o movimento.
  		$sSqlTipoTra    = $oDaoEmpAgeMovTipoTransmissao->sql_query(null,"e57_sequencial, e57_descricao", null,"e25_empagemov = {$iCodigoMovimento}");
  		$rsTipoTraAtual = $oDaoEmpAgeMovTipoTransmissao->sql_record($sSqlTipoTra);
  		if ($oDaoEmpAgeMovTipoTransmissao->numrows == 0) {
  			throw new BusinessException('ERRO [ 0 ] - Movimento sem vínculo com tipo de transmissão.');
  		}

  		$oDadosTipoCadastrado    = db_utils::fieldsMemory($rsTipoTraAtual, 0);
  		$iTipoAtual              = $oDadosTipoCadastrado->e57_sequencial;
  		$oValoresTipoTransmissao = new stdClass();
  		$oValoresTipoTransmissao->e57_sequencial = $oDadosTipoCadastrado->e57_sequencial;
  		$oValoresTipoTransmissao->e57_descricao  = urlencode($oDadosTipoCadastrado->e57_descricao);
  		$aTiposTransmissao[0] = $oValoresTipoTransmissao;

  		$sSqlTiposTransmissao = $oDaoEmpAgeTipoTransmissao->sql_query_file(null, "e57_sequencial, e57_descricao", 1, "e57_sequencial <> {$iTipoAtual}");
  		$rsTiposTransmissao   = $oDaoEmpAgeTipoTransmissao->sql_record($sSqlTiposTransmissao);
  		if ($oDaoEmpAgeTipoTransmissao->numrows > 0) {

  		  for ($iTipoTra = 0; $iTipoTra < $oDaoEmpAgeTipoTransmissao->numrows; $iTipoTra++) {

  		  	$oDadosTipoTra = db_utils::fieldsMemory($rsTiposTransmissao, $iTipoTra);
  		  	$oValoresTipoTransmissao = new stdClass();
  		  	$oValoresTipoTransmissao->e57_sequencial = $oDadosTipoTra->e57_sequencial;
  		  	$oValoresTipoTransmissao->e57_descricao  = urlencode($oDadosTipoTra->e57_descricao);
  		  	$aTiposTransmissao[] = $oValoresTipoTransmissao;
  		  }
  		}
  		$oRetorno->aDados = $aTiposTransmissao;
  		$oRetorno->iCodigoRecurso = $oParam->iCodigoRecurso;

  	break;


  	case "salvarDetalhes":

  		$iMovimento       = $oParam->iMovimento;
  		$aDetalhes        = $oParam->aDetalhes;
  		$iTipoTransmissao = $oParam->iTipoTransmissao;
      $lFatura          = $oParam->fatura;
      $nTotalFaturas = 0;
      
  		db_inicio_transacao();

  		$oDaoEmpAgeMovTipoTransmissao = db_utils::getDao('empagemovtipotransmissao');
  		$sSqlEmpAgeMovTipoTransmissao = $oDaoEmpAgeMovTipoTransmissao->sql_query_file (null, "*", null, "e25_empagemov = {$iMovimento}");
  		$rsEmpAgeMovTipoTransmissao   = $oDaoEmpAgeMovTipoTransmissao->sql_record($sSqlEmpAgeMovTipoTransmissao);
  		if ($oDaoEmpAgeMovTipoTransmissao->numrows == 0) {
  			throw new BusinessException("ERRO [ 0 ] - Não existe vinculo do Movimento com Tipo de Transmissão.");
  		}
  		$oDadosEmpAgeMovTipoTransmissao = db_utils::fieldsMemory($rsEmpAgeMovTipoTransmissao, 0);
  		$oDaoEmpAgeMovTipoTransmissao->e25_sequencial            = $oDadosEmpAgeMovTipoTransmissao->e25_sequencial;
  		$oDaoEmpAgeMovTipoTransmissao->e25_empagemov             = $oDadosEmpAgeMovTipoTransmissao->e25_empagemov;
  		$oDaoEmpAgeMovTipoTransmissao->e25_empagetipotransmissao = $iTipoTransmissao;
  		$oDaoEmpAgeMovTipoTransmissao->alterar($oDaoEmpAgeMovTipoTransmissao->e25_sequencial);
  		if ($oDaoEmpAgeMovTipoTransmissao->erro_status == 0) {
  			throw new DBException("ERRO [ 1 ] - Alterando vínculo do Movimento com Tipo de Transmissão " . $oDaoEmpAgeMovTipoTransmissao->erro_msg);
  		}

        /* [Inicio plugin GeracaoArquivoOBN] */
                $oDaoEmpAgeMovTipoTransmissaoFatura = db_utils::getDao("empagemovtipotransmissaofatura");
                $oDaoEmpAgeMovTipoTransmissaoFatura->empagemovtipotransmissao  = $oDadosEmpAgeMovTipoTransmissao->e25_sequencial;
                $oDaoEmpAgeMovTipoTransmissaoFatura->fatura                    = "{$oParam->fatura}";
                $rsEmpAgeMovTipoTransmissaoFatura = $oDaoEmpAgeMovTipoTransmissaoFatura->sql_record($oDaoEmpAgeMovTipoTransmissaoFatura->sql_query_file(null, "*", null, "empagemovtipotransmissao = {$oDadosEmpAgeMovTipoTransmissao->e25_sequencial}"));
                if ($oDaoEmpAgeMovTipoTransmissaoFatura->numrows == 0) {
                     
                    $oDaoEmpAgeMovTipoTransmissaoFatura->sequencial = null;
                    $oDaoEmpAgeMovTipoTransmissaoFatura->incluir();
                     
                } else {
                     
                    $oDadosEmpAgeMovTipoTransmissao->e25_sequencial = db_utils::fieldsMemory($rsEmpAgeMovTipoTransmissaoFatura,0)->sequencial;
                    $oDaoEmpAgeMovTipoTransmissaoFatura->alterar($oDadosEmpAgeMovTipoTransmissao->e25_sequencial);
                     
                }
                if ($oDaoEmpAgeMovTipoTransmissaoFatura->erro_status == 0) {
                    throw new DBException("ERRO [ 1 ] - Alterando informacao de fatura para o Movimento " . $oDaoEmpAgeMovTipoTransmissaoFatura->erro_msg);
                }
        
        /* [Fim plugin GeracaoArquivoOBN] */

  		/*
  		 * Desvinculamos os detalhes de codigos de barra
  		 */
      $oDaoEmpAgeMovDetalheTransmissao = db_utils::getDao("empagemovdetalhetransmissao");
      $oDaoEmpAgeMovDetalheTransmissao->excluir(null,"e74_empagemov = {$iMovimento}");
      if ($oDaoEmpAgeMovDetalheTransmissao->erro_status == '0') {

        $sMensagemErro  = "ERRO [ 2 ] - Desvinculando detalhes do tipo de transmissao do movimento - ";
        $sMensagemErro .= $oDaoEmpAgeMovDetalheTransmissao->erro_msg;
        throw new DBException($sMensagemErro);
      }

  		foreach ($aDetalhes as $oDetalhes) {

  		  $iTipoFatura = 2;
  		  if ($oDetalhes->iFatura == "Fatura") {
  		    $iTipoFatura = 1;
  		  } else if ($oDetalhes->iFatura == "GPS") {
          $iTipoFatura = 3;
          $iTipoPagamento = 1;
        } else if ($oDetalhes->iFatura == "DARF") {
          $iTipoFatura = 4;
          $iTipoPagamento = 2;
        } else if ($oDetalhes->iFatura == "DARF Simples") {
          $iTipoFatura = 5;
          $iTipoPagamento = 3;
        }

        $nTotalFaturas += str_replace(",", ".", (str_replace(".", "", $oDetalhes->nValor)));

        if ($iTipoFatura == 1 || $iTipoFatura == 2) {

    			$iCodigoBarras   = $oDetalhes->iCodigoBarras;
    			$iLinhaDigitavel = $oDetalhes->iLinhaDigitavel;
    			$nValor          = str_replace(",", ".", (str_replace(".", "", $oDetalhes->nValor)));
    			$nJuros          = str_replace(",", ".", (str_replace(".", "", $oDetalhes->nJuros)));
    			$nDesconto       = str_replace(",", ".", (str_replace(".", "", $oDetalhes->nDesconto)));
    			$dtData          = $oDetalhes->dtData;

  	  		$oDaoVerifica = new cl_empagemovdetalhetransmissao();
  	  		$sSqlVerifica = $oDaoVerifica->sql_query_busca_codigo_barras($iCodigoBarras, $iMovimento);
  	  		$rsVerifica   = $oDaoVerifica->sql_record($sSqlVerifica);

          if ($oDaoVerifica->numrows > 0) {
            throw new DBException('Código de barras "'.$iCodigoBarras.'" já lançado em outro movimento.');
          }

  	  		$oDaoEmpAgeMovDetalheTransmissao = new cl_empagemovdetalhetransmissao();
  	  		$oDaoEmpAgeMovDetalheTransmissao->e74_empagemov      = $iMovimento;
  	  		$oDaoEmpAgeMovDetalheTransmissao->e74_codigodebarra  = $iCodigoBarras;
  	  		$oDaoEmpAgeMovDetalheTransmissao->e74_valornominal   = number_format($nValor, 2, '.', '');
  	  		$oDaoEmpAgeMovDetalheTransmissao->e74_datavencimento = $dtData;
  	  		$oDaoEmpAgeMovDetalheTransmissao->e74_valorjuros     = number_format($nJuros, 2, '.', '');
  	  		$oDaoEmpAgeMovDetalheTransmissao->e74_valordesconto  = number_format($nDesconto, 2, '.', '');
  	  		$oDaoEmpAgeMovDetalheTransmissao->e74_tipofatura     = $iTipoFatura;
  	  		$oDaoEmpAgeMovDetalheTransmissao->e74_linhadigitavel = $iLinhaDigitavel;
  	  		$oDaoEmpAgeMovDetalheTransmissao->incluir(null);
  	  		if ($oDaoEmpAgeMovDetalheTransmissao->erro_status == 0) {
  	  			throw new DBException("ERRO [ 1 ] - Incluindo detalhe - " .  $oDaoEmpAgeMovDetalheTransmissao->erro_msg);
  	  		}
        } else {
          $iCodReceita           = $oDetalhes->iCodReceita;
          $iCodIdent             = $oDetalhes->iCodIdent;
          $iNumReferencia        = $oDetalhes->iNumReferencia;
          $sMesAnoCompetencia    = $oDetalhes->sMesAnoCompetencia;
          $dtPeriodoApuracao     = ($oDetalhes->dtPeriodoApuracao == "null") ? "" : $oDetalhes->dtPeriodoApuracao;
          $dtDataVencimento      = ($oDetalhes->dtData == "null" || $oDetalhes->dtData == "-") ? "" : $oDetalhes->dtData; //comparo se é '-' porque é usado assim para não exibir 'null' na tela
          $nValorINSS            = str_replace(",", ".", (str_replace(".", "", $oDetalhes->nValorINSS)));
          $nValorOutras          = str_replace(",", ".", (str_replace(".", "", $oDetalhes->nValorOutras)));
          $nAtualizacaoMonetaria = str_replace(",", ".", (str_replace(".", "", $oDetalhes->nAtualizacaoMonetaria)));
          $nValorReceitaBruta    = str_replace(",", ".", (str_replace(".", "", $oDetalhes->nValorReceitaBruta)));
          $nValorPrincipal       = str_replace(",", ".", (str_replace(".", "", $oDetalhes->nValor)));
          $nValorMulta           = str_replace(",", ".", (str_replace(".", "", $oDetalhes->nValorMulta)));
          $nPercentualReceita    = str_replace(",", ".", (str_replace(".", "", $oDetalhes->nPercentualReceita)));
          $nJurosEncargos        = str_replace(",", ".", (str_replace(".", "", $oDetalhes->nJurosEncargos)));

          $oDaoEmpageMovPagamento = new cl_empagemovpagamento();
          $oDaoEmpageMovPagamento->empagemov            = $iMovimento; 
          $oDaoEmpageMovPagamento->tipopagamento        = $iTipoPagamento;
          $oDaoEmpageMovPagamento->codreceita           = $iCodReceita;
          $oDaoEmpageMovPagamento->codidentificacao     = $iCodIdent; 
          $oDaoEmpageMovPagamento->periodoapuracao      = $dtPeriodoApuracao; 
          $oDaoEmpageMovPagamento->datavencimento       = $dtDataVencimento; 
          $oDaoEmpageMovPagamento->mesanocompetencia    = $sMesAnoCompetencia; 
          $oDaoEmpageMovPagamento->numreferencia        = $iNumReferencia;
          $oDaoEmpageMovPagamento->valorINSS            = $nValorINSS;
          $oDaoEmpageMovPagamento->valoroutras          = $nValorOutras;
          $oDaoEmpageMovPagamento->atualizacaomonetaria = $nAtualizacaoMonetaria;
          $oDaoEmpageMovPagamento->valorreceitabruta    = $nValorReceitaBruta;  
          $oDaoEmpageMovPagamento->percentualreceita    = $nPercentualReceita;   
          $oDaoEmpageMovPagamento->valorprincipal       = $nValorPrincipal;
          $oDaoEmpageMovPagamento->valormulta           = $nValorMulta; 
          $oDaoEmpageMovPagamento->jurosencargos        = $nJurosEncargos; 
          $oDaoEmpageMovPagamento->incluir(null);
          if ($oDaoEmpageMovPagamento->erro_status == 0) {
            throw new DBException("ERRO [ 1 ] - Incluindo detalhe - " .  $oDaoEmpageMovPagamento->erro_msg);
          }
        }
  		}

      $oDaoEmpAgeMov = db_utils::getDao('empagemov');
      $sSqlEmpAgeMov = $oDaoEmpAgeMov->sql_query_file (null, "*", null, "e81_codmov = {$iMovimento}");
      $rsEmpAgeMov   = $oDaoEmpAgeMov->sql_record($sSqlEmpAgeMov);
      $nValorMov     = db_utils::fieldsMemory($rsEmpAgeMov, 0)->e81_valor;

      if($lFatura == 'f' && $nTotalFaturas > 0 && round($nTotalFaturas, 2) != round($nValorMov, 2)) {
        throw new DBException("Valor total das faturas deve ser igual ao valor do movimento: ". $nValorMov);
      }

  		db_fim_transacao(false);
  		$oRetorno->sMessage   = 'Movimento(s) salvo(s) com sucesso.';
  		$oRetorno->iMovimento = $iMovimento;

  	break;


  	case "getRecursoFundeb":

  	  $iCodigoRecursoFundeb = ParametroCaixa::getCodigoRecursoFUNDEB(db_getsession('DB_instit'));
	    $oRetorno->iCodigoRecurso = $iCodigoRecursoFundeb;

  	  break;

    default:
      throw new ParameterException("Nenhuma Opção Definida");
    break;

  }

  $oRetorno->sMessage = urlencode($oRetorno->sMessage);
  echo $oJson->encode($oRetorno);

} catch (Exception $eErro){

  $oRetorno->iStatus  = 2;
  $oRetorno->sMessage = urlencode($eErro->getMessage());
  echo $oJson->encode($oRetorno);

}catch (DBException $eErro){

  db_fim_transacao(true);
  $oRetorno->iStatus  = 2;
  $oRetorno->sMessage = urlencode($eErro->getMessage());
  echo $oJson->encode($oRetorno);

}catch (ParameterException $eErro){

  $oRetorno->iStatus  = 2;
  $oRetorno->sMessage = urlencode($eErro->getMessage());
  echo $oJson->encode($oRetorno);

}catch (BusinessException $eErro){

  db_fim_transacao(true);
  $oRetorno->iStatus  = 2;
  $oRetorno->sMessage = urlencode($eErro->getMessage());
  echo $oJson->encode($oRetorno);
}

?>
