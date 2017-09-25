<?php
$head1 = "AUTORIZAÇÕES BANCÁRIAS";

$rsEmpageGera = $clempagegera->sql_record($clempagegera->sql_query_file($e87_codgera));
$oDadosGeracao = db_utils::fieldsMemory($rsEmpageGera,0);
$head3 = "Relação Externa :  ".number_format($oDadosGeracao->e87_codgera,0,',','.');
$head3 .= " / ".substr($oDadosGeracao->e87_dataproc, 0,4);
$head3 .= " - {$oDadosGeracao->e87_descgera}";

$xtipo = '';

$pdf = new PDF();
$pdf->Open();
$pdf->AliasNbPages();
$pdf->setfillcolor(235);

$total = 0;
$alt = 4;

$sql_unid_gestora_emp = "select distinct 
                                o41_orgao||lpad(o41_unidade,2,'0') as unid_gestora, 
                                o41_orgao as orgao, 
                                o41_unidade as unidade, 
                                o41_descr as descr_unidade,
                                extract(year from e87_data) as exercicio_geracao
                           from empageconfgera 
                                inner join empagegera    on e90_codgera  = e87_codgera 
                                inner join empagemov     on e81_codmov   = e90_codmov 
                                inner join empempenho    on e81_numemp   = e60_numemp 
	                            inner join empagepag     on e81_codmov   = e85_codmov
                                inner join empagetipo    on e85_codtipo  = e83_codtipo
	                            inner join empord        on e81_codmov   = e82_codmov
                                inner join orcdotacao    on e60_coddot   = o58_coddot 
                                                        and o58_anousu   = e60_anousu 
                                inner join orcunidade    on o58_orgao    = o41_orgao 
                                                        and o58_unidade  = o41_unidade 
                                                        and o58_anousu   = o41_anousu 
  	                            inner join db_permemp    on o41_orgao    = db20_orgao
  	                            inner join db_usupermemp on db20_codperm = db21_codperm
                          where empagegera.e87_codgera in ('$e87_codgera') and db21_id_usuario =".db_getsession("DB_id_usuario");

$sql_unid_gestora_slip = "select distinct o41_orgao||lpad(o41_unidade,2,'0') as unid_gestora, 
	                             o41_orgao as orgao, 
	                             o41_unidade as unidade, 
	                             o41_descr as descr_unidade,
		                         extract(year from e87_data) as exercicio_geracao
                            from empageconfgera
                                 inner join empagegera               on e90_codgera              = e87_codgera
                                 inner join empagemov                on e90_codmov               = e81_codmov
                                 inner join empagemovtipotransmissao on e25_empagemov            = e81_codmov
                                 inner join empage                   on empage.e80_codage        = empagemov.e81_codage
                                 inner join empagepag                on e81_codmov               = e85_codmov
                                 inner join empagetipo               on e85_codtipo              = e83_codtipo
                                 inner join empageslip               on e81_codmov               = e89_codmov
                                 inner join slip                     on slip.k17_codigo          = e89_codigo
                                 inner join slipnum                  on slipnum.k17_codigo       = slip.k17_codigo
                                 inner join conplanoreduz            on c61_codcon in (select c61_codcon 
		                                                                                 from conplanoreduz 
		                                                                                where c61_reduz = e83_conta)
                                                                    and c61_anousu               = extract(year from k17_data) 
                                 inner join conplanoconta            on c63_codcon               = c61_codcon
                                                                    and c63_anousu               = c61_anousu
                                 inner join plugins.slipdepartamento dep on slip                 = slip.k17_codigo
                                 inner join db_depart                    on dep.departamento     = db_depart.coddepto
                                 inner join db_depusu                    on db_depart.coddepto   = db_depusu.coddepto 
		                                                                and db_depusu.id_usuario = ".db_getsession("DB_id_usuario")."
                                 inner join db_departorg                 on db_depart.coddepto   = db01_coddepto 
                                                                        and db01_anousu          = c61_anousu 
                                 inner join orcunidade                   on db01_orgao           = o41_orgao 
                                                                        and db01_unidade         = o41_unidade 
                                                                        and db01_anousu          = o41_anousu
                           where empagegera.e87_codgera in ('$e87_codgera')";
$sql_unid_gestora = $sql_unid_gestora_emp." union ".$sql_unid_gestora_slip;
$rs_unid_gestora = db_query($sql_unid_gestora);

for ($x =0 ; $x < pg_numrows($rs_unid_gestora);$x++) {
	
db_fieldsmemory($rs_unid_gestora,$x);

$db_where ='';
if(isset($e87_codgera) && trim($e87_codgera)!=""){
  $db_where = ' empagegera.e87_codgera in ('.$e87_codgera.')';
} else {
  $db_where = ' e80_instit = ' . db_getsession("DB_instit");
}

if (isset($lCancelado) && $lCancelado == "0") {


  $db_where .= " and empageconfgera.e90_cancelado is false ";
}

$sqlOrdem = "select  distinct
                     e90_codgera,
                     e90_codmov,
                     e87_data,
                     e87_dataproc,
                     c63_banco,
                     c63_agencia,
                     coalesce(c63_dvagencia,'0') as c63_dvagencia,
                     c63_conta,
                     coalesce(c63_dvconta,'0') as c63_dvconta,
                     pc63_agencia::varchar,
                     coalesce(pc63_agencia_dig,'0') as pc63_agencia_dig,
                     pc63_conta::varchar,
                     pc63_contabanco::varchar,
                     coalesce(pc63_conta_dig,'0') as pc63_conta_dig,
                     translate(to_char(round(e81_valor- coalesce((select coalesce(sum(e23_valorretencao),0)::numeric as valorRetido                   
                                                                    from retencaoreceitas                                                            
                                                                         inner join retencaopagordem  on e23_retencaopagordem = e20_sequencial       
                                                                         inner join retencaotiporec   on e23_retencaotiporec  = e21_sequencial       
                                                                         inner join pagordemnota      on e20_pagordem         = e71_codord           
                                                                         inner join retencaoempagemov on e27_retencaoreceitas = e23_sequencial       
                                                                   where e27_empagemov = e81_codmov                                                 
                                                                     and e23_ativo     is true                                                        
                                                                     and e27_principal is true),0),2),'99999999999.99'),'.','') as valor,
                     e81_valor- coalesce((select coalesce(sum(e23_valorretencao),0)::numeric as valorRetido                   
                                            from retencaoreceitas                                                            
                                                 inner join retencaopagordem  on e23_retencaopagordem = e20_sequencial       
                                                 inner join retencaotiporec   on e23_retencaotiporec  = e21_sequencial       
                                                 inner join pagordemnota      on e20_pagordem         = e71_codord           
                                                 inner join retencaoempagemov on e27_retencaoreceitas = e23_sequencial       
                                           where e27_empagemov = e81_codmov                                                 
                                             and e23_ativo     is true                                                        
                                             and e27_principal is true),0) as valorori,
	                 case when  pc63_banco = c63_banco then '01' else '03' end as  lanc,
	                 coalesce(pc63_banco,'000') as pc63_banco,
	                 e83_convenio as convenio,
	                 z01_numcgm as z01_numcgm,
	                 substr(z01_nome,1,40) as z01_nome,
	                 case when pc63_cnpjcpf = '0' or trim(pc63_cnpjcpf) = '' or pc63_cnpjcpf is null then length(trim(z01_cgccpf)) else length(trim(pc63_cnpjcpf)) end as tam,
	                 case when  pc63_cnpjcpf = '0' or trim(pc63_cnpjcpf) = '' or pc63_cnpjcpf is null then z01_cgccpf else pc63_cnpjcpf end as cnpj,
	                 e88_codmov as cancelado,
	                 z01_ender,
	                 z01_numero,
	                 z01_compl,
	                 z01_bairro,
	                 z01_munic,
	                 z01_cep,
	                 z01_uf,
	                 empagetipo.*,
	                 e85_codtipo,
	                 e81_valor,
	                 e81_codmov,
	                 e81_numemp,
	                 e87_dataproc as dataprocessa,
	                 e87_hora,
	                 pc63_dataconf,
	                 e60_codemp,
	                 e82_codord,
	                 (select coalesce(sum(e23_valorretencao),0)::numeric as valorRetido                   
                        from retencaoreceitas                                                            
                             inner join retencaopagordem  on e23_retencaopagordem = e20_sequencial       
                             inner join retencaotiporec   on e23_retencaotiporec  = e21_sequencial       
                             inner join pagordemnota      on e20_pagordem         = e71_codord           
                             inner join retencaoempagemov on e27_retencaoreceitas = e23_sequencial       
                       where e27_empagemov = e81_codmov                                                 
                         and e23_ativo     is true                                                        
                         and e27_principal is true) as vlrretencao,
	                 case when fatura = 't' then 2 else 1 end as comfatura,
	                 e74_codigodebarra as codigobarras,
                     null::integer as slipvinculo, 
		             '' as finalidadepagamentofundeb,
		             codreceita
                from empageconfgera
	                 inner join empagegera                             on e90_codgera       = e87_codgera
	                 inner join empagemov                              on e90_codmov        = e81_codmov
	                 inner join empagemovtipotransmissao               on e25_empagemov     = e81_codmov
                      left join plugins.empagemovtipotransmissaofatura on empagemovtipotransmissaofatura.empagemovtipotransmissao = e25_sequencial
                     inner join empage                                 on empage.e80_codage = empagemov.e81_codage
	                 inner join empempenho                             on e60_numemp        = e81_numemp
	                 inner join orcdotacao                             on e60_coddot        = o58_coddot and e60_anousu = o58_anousu
	                 inner join empagepag                              on e81_codmov        = e85_codmov
	                 inner join empagetipo                             on e85_codtipo       = e83_codtipo
	                 inner join empord                                 on e81_codmov        = e82_codmov
	                  left join empageslip                             on e81_codmov        = e89_codmov
	                  left join (select distinct on (e74_empagemov) *
                                   from empagemovdetalhetransmissao ) as empagemovdetalhetransmissao on  empagemovdetalhetransmissao.e74_empagemov = empagemov.e81_codmov
                      left join (select distinct on (empagemov) *
                                   from plugins.empagemovpagamento  ) as empagemovpagamento on empagemovpagamento.empagemov = empagemov.e81_codmov
	                 inner join conplanoreduz on c61_codcon in (select c61_codcon from conplanoreduz where c61_reduz = e83_conta) and c61_anousu = ".db_getsession("DB_anousu")."
	                 inner join conplanoconta on c63_codcon = c61_codcon 
	                                         and c63_anousu = c61_anousu
	                  left join slip on slip.k17_codigo = e89_codigo
	                  left join slipnum on slipnum.k17_codigo = slip.k17_codigo
	                  left join empageconfcanc on e88_codmov = e90_codmov
	                  left join empagemovconta on e90_codmov = e98_codmov
	                  left join pcfornecon on pc63_contabanco = e98_contabanco
	                  left join cgm on z01_numcgm = pc63_numcgm
               where (o58_orgao = $orgao or $orgao is null) 
                 and (o58_unidade = $unidade or $unidade is null) 
                 and e80_instit = " . db_getsession("DB_instit") . "
                 and {$db_where} ";
  $sqlSlip = " select distinct
	                  e90_codgera,
	                  e90_codmov,
	                  e87_data,
	                  e87_dataproc,
	                  conplanoconta.c63_banco,
	                  conplanoconta.c63_agencia,
	                  coalesce(conplanoconta.c63_dvagencia,'0') as c63_dvagencia,
	                  conplanoconta.c63_conta,
	                  coalesce(conplanoconta.c63_dvconta,'0') as c63_dvconta,

                      (case when pc63_agencia is null 
  		                      then descrconta.c63_agencia
                            else pc63_agencia end )::varchar as pc63_agencia,
                  
                      coalesce((case when pc63_agencia_dig is null 
  		                               then descrconta.c63_dvagencia
                                     else pc63_agencia_dig end ),'0')::varchar as pc63_agencia_dig,
                  
                  	  (case when pc63_conta is null 
  		                      then descrconta.c63_conta
                            else pc63_conta end )::varchar as pc63_conta,

                      null as pc63_contabanco,

                      coalesce((case when pc63_conta_dig is null 
  		                               then descrconta.c63_dvconta
                                     else pc63_conta_dig end ),'0')::varchar as pc63_conta_dig,

                      translate(to_char(round(e81_valor- coalesce((select coalesce(sum(e23_valorretencao),0)::numeric as valorRetido                   
                                                                     from retencaoreceitas                                                            
                                                                          inner join retencaopagordem  on e23_retencaopagordem = e20_sequencial       
                                                                          inner join retencaotiporec   on e23_retencaotiporec  = e21_sequencial       
                                                                          inner join pagordemnota      on e20_pagordem         = e71_codord           
                                                                          inner join retencaoempagemov on e27_retencaoreceitas = e23_sequencial       
                                                                    where e27_empagemov = e81_codmov                                                 
                                                                      and e23_ativo     is true                                                        
                                                                      and e27_principal is true),0),2),'99999999999.99'),'.','') as valor,
                      e81_valor- coalesce((select coalesce(sum(e23_valorretencao),0)::numeric as valorRetido                   
                                             from retencaoreceitas                                                            
                                                  inner join retencaopagordem  on e23_retencaopagordem = e20_sequencial       
                                                  inner join retencaotiporec   on e23_retencaotiporec  = e21_sequencial       
                                                  inner join pagordemnota      on e20_pagordem         = e71_codord           
                                                  inner join retencaoempagemov on e27_retencaoreceitas = e23_sequencial       
                                            where e27_empagemov = e81_codmov                                                 
                                              and e23_ativo     is true                                                        
                                              and e27_principal is true),0) as valorori,
	  case when  (pc63_banco = conplanoconta.c63_banco or descrconta.c63_banco = conplanoconta.c63_banco)
	       then '01' else '03' end as  lanc,

	  coalesce(pc63_banco, descrconta.c63_banco) as pc63_banco,

	  e83_convenio as convenio,
	  case when cgm.z01_numcgm is null then cgmslip.z01_numcgm else cgm.z01_numcgm end as z01_numcgm,
	  --(case when descrconta.c63_conta = '9347' then 'FUNDACAO CULTURAL CAPITANIA DAS ARTES'
	  --	  when descrconta.c63_conta = '9384' then 'GABINETE DO PREFEITO'  else cgm.z01_nome end) as z01_nome,
	  substr(case when cgm.z01_nome is null then cgmslip.z01_nome else cgm.z01_nome end,1,40) as z01_nome,
	  --'FUNDACAO CULTURAL CAPITANIA DAS ARTES' as z01_nome ,
	  --case when  pc63_cnpjcpf = '0' or trim(pc63_cnpjcpf) = '' or pc63_cnpjcpf is null
	  --     then length(trim( case when cgm.z01_cgccpf is null then cgmslip.z01_cgccpf else cgm.z01_cgccpf end))
	  --     else length(trim(pc63_cnpjcpf)) end as tam,
	  (case when trim(pc63_cnpjcpf) = '0' or trim(pc63_cnpjcpf) = '' or pc63_cnpjcpf is null then
		 length(trim( 
				(case when descrconta.c63_identificador is null then
					case when cgm.z01_cgccpf is null then cgmslip.z01_cgccpf else cgm.z01_cgccpf end
				else
					descrconta.c63_identificador 
				end
				)
			    )
			)
		 else length(trim(pc63_cnpjcpf)) end) as tam,
	  --case when  pc63_cnpjcpf = '0' or trim(pc63_cnpjcpf) = '' or pc63_cnpjcpf is
	  --     null then
	  --       ( case when cgm.z01_cgccpf is null then cgmslip.z01_cgccpf else cgm.z01_cgccpf end)
	  --     else pc63_cnpjcpf end as cnpj,
	  (case when trim(pc63_cnpjcpf) = '0' or trim(pc63_cnpjcpf) = '' or pc63_cnpjcpf is null then
                                  (case when descrconta.c63_identificador is null then
                                        (case when cgm.z01_cgccpf is null then cgmslip.z01_cgccpf else cgm.z01_cgccpf end)
                                   else
                                        descrconta.c63_identificador end)
                            else pc63_cnpjcpf end) as cnpj,
	  e88_codmov as cancelado,
	  case when cgm.z01_ender is null then cgmslip.z01_ender else cgm.z01_ender end as z01_ender,
	  case when cgm.z01_numero is null then cgmslip.z01_numero else cgm.z01_numero end as z01_numero,
	  case when cgm.z01_compl is null then cgmslip.z01_compl else cgm.z01_compl end as z01_compl,
	  case when cgm.z01_bairro is null then cgmslip.z01_bairro else cgm.z01_bairro end as z01_bairro,
	  case when cgm.z01_munic is null then cgmslip.z01_munic else cgm.z01_munic end as z01_munic,
	  case when cgm.z01_cep is null then cgmslip.z01_cep else cgm.z01_cep end as z01_cep,
	  case when cgm.z01_uf is null then cgmslip.z01_uf else cgm.z01_uf end as z01_uf,
	  empagetipo.*,
	  e85_codtipo,
	  e81_valor,
	  e81_codmov,
	  e81_numemp,
	  e87_dataproc as dataprocessa,
	  e87_hora,
	  pc63_dataconf,
	  'slip' as e60_codemp,
	  e89_codigo as e82_codord,
	  0 as  vlrretencao,
	case when fatura = 't' then 2 else 1 end as comfatura,
	e74_codigodebarra as codigobarras,
        --sliptipooperacaovinculo.k153_slipoperacaotipo as slipvinculo
        (case when ar.slip is null then sliptipooperacaovinculo.k153_slipoperacaotipo else 1 end) as slipvinculo,
	  e151_codigo||' - '||e151_descricao as finalidadepagamentofundeb, 
  		codreceita
  from empageconfgera
       inner join empagegera               on e90_codgera        = e87_codgera
       inner join empagemov                on e90_codmov         = e81_codmov
       inner join empagemovtipotransmissao on e25_empagemov = e81_codmov
        left join plugins.empagemovtipotransmissaofatura on empagemovtipotransmissaofatura.empagemovtipotransmissao = e25_sequencial 
       inner join empage                   on empage.e80_codage  = empagemov.e81_codage
       inner join empagepag                on e81_codmov         = e85_codmov
       inner join empagetipo               on e85_codtipo        = e83_codtipo
       inner join empageslip               on e81_codmov         = e89_codmov
       inner join conplanoreduz            on c61_reduz          = e83_conta
                                          and c61_anousu         = ".db_getsession("DB_anousu")."
       inner join conplanoconta            on c63_codcon         = c61_codcon
                                          and c63_anousu         = c61_anousu
       inner join slip                     on slip.k17_codigo    = e89_codigo
       inner join sliptipooperacaovinculo         on slip.k17_codigo = sliptipooperacaovinculo.k153_slip
       inner join slipnum                  on slipnum.k17_codigo = slip.k17_codigo
	inner join plugins.saltesdepart on c61_reduz = saltes
	inner join db_depart on depart = coddepto
	inner join db_depusu on db_depart.coddepto = db_depusu.coddepto and db_depusu.id_usuario = ".db_getsession("DB_id_usuario")."
	
	   left join slipfinalidadepagamentofundeb on e153_slip = slip.k17_codigo
	   left join finalidadepagamentofundeb on e151_sequencial = e153_finalidadepagamentofundeb
			
       left join empageconfcanc            on e88_codmov         = e90_codmov
       left join empagemovconta            on e90_codmov         = e98_codmov
       left join pcfornecon                on pc63_contabanco    = e98_contabanco
       left join cgm                       on z01_numcgm         = pc63_numcgm
       left join cgm cgmslip               on cgmslip.z01_numcgm = slipnum.k17_numcgm

       left join conplanoreduz cre         on cre.c61_reduz      = k17_debito
                                          and cre.c61_anousu     = ".db_getsession("DB_anousu")."

       left join conplano concre           on concre.c60_codcon  = cre.c61_codcon
                                          and concre.c60_anousu  = cre.c61_anousu

       left join conplanoconta descrconta  on concre.c60_codcon  = descrconta.c63_codcon
                                          and concre.c60_anousu  = descrconta.c63_anousu
       left join plugins.autorizacaorepasse ar on slip.k17_codigo = ar.slip
       left join (select  distinct on (e74_empagemov) *
                  from empagemovdetalhetransmissao )
                as empagemovdetalhetransmissao on  empagemovdetalhetransmissao.e74_empagemov = empagemov.e81_codmov
       left join (select  distinct on (empagemov) * 
                                 from plugins.empagemovpagamento)
                                as empagemovpagamento on empagemovpagamento.empagemov = empagemov.e81_codmov
  where e80_instit = " . db_getsession("DB_instit") . " and  $db_where
  order by e85_codtipo,z01_nome,pc63_banco,pc63_agencia";
	$sqlMov = $sqlOrdem." union ".$sqlSlip;

$result_empagegera = $clempagegera->sql_record($sqlMov);
$numrows_empagegera = $clempagegera->numrows;
if($numrows_empagegera==0){
  db_redireciona("db_erros.php?fechar=true&db_erro=Nenhum registro encontrado.");
}
db_fieldsmemory($result_empagegera,0);


$oConlancamEmp = new cl_conlancamemp();
$sWhere  = " c75_numemp = {$e81_numemp} ";
$sWhere .= " and c53_tipo   = 30 order by c75_codlan desc limit 1";
$sSqlLancamentoEmpenho = $oConlancamEmp->sql_query_documentos(null, 'c75_data', null, $sWhere);
$rsBuscaLancamento = db_query($sSqlLancamentoEmpenho);
$dtPagamento = $dataprocessa;
if ($rsBuscaLancamento) {
  $dtPagamento = db_utils::fieldsMemory($rsBuscaLancamento, 0)->c75_data;
}

$head5 = "Data de Emissão  :  ". db_formatar($e87_dataproc,"d").' AS '.$e87_hora.' HS';

// seleciona o nome do banco
$sql = "select db90_descr from db_bancos where trim(db90_codban)= '$c63_banco'";
$rbanco = db_query($sql);
if (pg_numrows($rbanco) > 0 ){
   db_fieldsmemory($rbanco,0);
}

if($c63_banco == '041'){
  $head7 = 'BANCO : 041 - BANRISUL';
}elseif($c63_banco == '001'){
  $head7 = 'BANCO : 001 - BANCO DO BRASIL';
}else{
  $head7 = 'BANCO ('.$c63_banco.'): '.$db90_descr;
}

$pdf->addpage("L");
$xvalor    = 0;
$xvaltotal = 0;
$xbanco    = '';
$ant_codgera = "";
$total_geral =0;

$soma_dep = 0;
$soma_doc = 0;
$soma_ted = 0;
$tota_dep = 0;
$tota_doc = 0;
$tota_ted = 0;

$nTotalBruto = 0;
$nTotalRetencoes = 0;
$tipooperacao = 0;

for($i =0 ; $i < $numrows_empagegera;$i++) {

  db_fieldsmemory($result_empagegera,$i);
  // Se o Movimento estiver marcado como fatura (Sim = 2) então tipo = 33
    // Movimentação com código de barras
    if (!empty($codigobarras)) {

      $tipooperacao = 38;

    } else {

	    // SLIP
	    if ($e60_codemp == "slip") {

		  if ($pc63_banco == "001") {
		  	
		  	if ($slipvinculo == 13) { // Retenção BB
		  	   if ($comfatura == 2) {
                                $tipooperacao = 33;
                             } else {
                                  $tipooperacao = 32;
                             }
		  	} else {
          
		  		if ($c63_conta.$c63_dvconta == "70009") {
		  		   $tipooperacao = 17;
		  		} else {
		  		   $tipooperacao = 37;
		  		}
		  	}
		  	
		  } else { 
		     $tipooperacao = 31;
		  }

	    } else {
	    	
	      // EMPENHO
		  if ($pc63_banco == "001") {
		  
		    if ($comfatura == 2) {
		      $tipooperacao = 33;
		    } else {
		      $tipooperacao = 32;
		    }
          
		  } else {
		    
		    if ($comfatura == 2) {
		      $tipooperacao = 33;
		    } else {
		      $tipooperacao = 31;
		    }
          
		  }
		  
	    }
	    
	    if (!empty($codreceita)) {
	    	$tipooperacao  = 39;
	    }

    }
  $e81_valor -= $vlrretencao;

  $pdf->setfont('arial','b',8);
  if($pdf->gety() > $pdf->h - 30 || $i==0){
    if($pdf->gety() > $pdf->h - 30){
      $pdf->cell(260,0.1,"","T",1,"L",0);
      $pdf->addpage("L");
    }

    $pdf->ln(3);
    
    $pdf->cell(250,$alt,"AUTORIZAÇÕES BANCÁRIAS",0,0,"C",0);
    $pdf->ln(5);
    $pdf->text($pdf->getx(),$pdf->gety(),"__________________________________________________________________________________________________________________________________________________________________________________",4);
    $pdf->ln(10);
    if ($c63_conta.$c63_dvconta == "70009") {
	$unid_gestora = "25000";
	$descr_unidade = "SEMPLA / PMN CONTA ÚNICA";
        $pdf->text($pdf->getx(),$pdf->gety(),"Unidade Gestora / Gestão: 0{$unid_gestora}00001 - {$descr_unidade}",4);
    } else {
        //$pdf->text($pdf->getx(),$pdf->gety(),"Unidade Gestora / Gestão: 00250100001 - GABINETE DO SECRETARIO / SEMPLA",4);
        $pdf->text($pdf->getx(),$pdf->gety(),"Unidade Gestora / Gestão: 00{$unid_gestora}00001 - {$descr_unidade}",4);
    }
    $pdf->ln(3);

    $pdf->cell(55,$alt,"Origem",1,0,"C",1);
    $pdf->cell(224,$alt,"Favorecido",1,1,"C",1);
    $pdf->cell(18,$alt, 'Documento',1,0,"C",0);
    $pdf->cell(15,$alt,"Agencia",1,0,"C",0);
    $pdf->cell(15,$alt,"Conta",1,0,"C",0);
    $pdf->cell(10,$alt,"Tipo",1,0,"C",0);
    $pdf->cell(91,$alt,$RLz01_nome,1,0,"C",0);
    $pdf->cell(25,$alt,$RLz01_cgccpf,1,0,"C",0);
    $pdf->cell(15,$alt,$RLpc63_banco,1,0,"C",0);
    $pdf->cell(15,$alt,$RLpc63_agencia,1,0,"C",0);
    $pdf->cell(20,$alt,$RLpc63_conta,1,0,"C",0);
    $pdf->cell(20,$alt,"OP/Slip",1,0,"C",0);
    $pdf->cell(25,$alt,"Valor (R$)",1,0,"C",0);
    $pdf->cell(10,$alt,"Canc.",1,1,"C",0);
  }
  if($ant_codgera!=$e85_codtipo.'-'.$e87_codgera){

    if($i !=0){

      $pdf->cell(249,$alt,'Total Conta',1,0,"C",1);
      $pdf->cell(30,$alt,db_formatar($xtotal,'f'),1,1,"R",1);
      $soma_dep = 0;
      $soma_doc = 0;
      $soma_ted = 0;

      $pdf->ln(3);
    }
    $pdf->ln(3);
    $pdf->cell(15,$alt,$e85_codtipo,1,0,"C",1);
    $pdf->cell(224,$alt,$e83_descr." - CONTA $e83_conta","LTB",0,"L",1);
    $pdf->cell(40,$alt,"","RTB",1,"L",1);
    $xtotal = 0;
    $ant_codgera=$e85_codtipo.'-'.$e87_codgera;
  }
  if ( $pc63_banco == $c63_banco ) {
    $codpgto   = "DEP";
    $soma_dep += $e81_valor;
    $tota_dep += $e81_valor;
  } else {
    if ( $e81_valor < 3000 ){
      $codpgto   = "DOC";
      $soma_doc += $e81_valor;
      $tota_doc += $e81_valor;
    } else {
      $codpgto   = "TED";
      $soma_ted += $e81_valor;
      $tota_ted += $e81_valor;
    }
  }

  if(trim($pc63_agencia_dig)!=""){
    $pc63_agencia_dig = "-".$pc63_agencia_dig;
  }
  if(trim($pc63_conta_dig)!=""){
    $pc63_conta_dig = "-".$pc63_conta_dig;
  }
  $pdf->setfont('arial','',7);
  $pdf->cell(18,$alt,$e81_codmov,1,0,"C",0);
  $pdf->cell(15,$alt,$c63_agencia.'-'.$c63_dvagencia,1,0,"C",0);
  $pdf->cell(15,$alt,$c63_conta.'-'.$c63_dvconta,1,0,"C",0);
  $pdf->cell(10,$alt,$tipooperacao,1,0,"C",0);

  $asteriscos = "";
  $result_asteriscos = $clempagemovconta->sql_record($clempagemovconta->sql_query_conta(null,"pc63_contabanco","","pc63_contabanco=$pc63_contabanco and e90_codmov is not null"));
  if($clempagemovconta->numrows > 0 || $pc63_dataconf!=""){
    $asteriscos = "** ";
  }

  $pdf->cell(91,$alt,$asteriscos.$z01_nome,1,0,"L",0);
  $pdf->cell(25,$alt,$cnpj,1,0,"C",0);

  $pdf->cell(15,$alt,$pc63_banco,1,0,"C",0);
  $pdf->cell(15,$alt,$pc63_agencia.$pc63_agencia_dig,1,0,"C",0);
  $pdf->cell(20,$alt,$pc63_conta.$pc63_conta_dig,1,0,"C",0);
  $pdf->cell(20,$alt,$e82_codord,1,0,"C",0); 
  $pdf->cell(25,$alt,db_formatar($e81_valor,'f'),1,0,"C",0);
  $pdf->cell(10,$alt,"(     )",1,1,"C",0);
  
  
  if (!empty($finalidadepagamentofundeb)) {
  	$pdf->cell(5,$alt,'',"LT",0,"L",0);
  	$pdf->cell(274,$alt,"Finalidade: ".$finalidadepagamentofundeb,"RT",1,"L",0);
  	$pdf->cell(279,1,'',"LR",1,"L",0);
  }
  

  $total++;
  $xtotal    += $e81_valor;
  $xvaltotal += $e81_valor;

  $nTotalRetencoes += $vlrretencao;
}

$nTotalBruto = $xvaltotal + $nTotalRetencoes;

$pdf->setfont('arial','b',8);
$pdf->cell(249,$alt,'Total Conta',1,0,"C",1);
$pdf->cell(30,$alt,db_formatar($xtotal,'f'),1,0,"R",1);
$pdf->ln(2);

$pdf->ln(2);
$pdf->cell(249,$alt,'Total Geral',1,0,"C",1);
$pdf->cell(30,$alt,db_formatar($xvaltotal,'f'),1,0,"R",1);

$pdf->ln(20);

$texto_aut = "Autorizamos o Banco do Brasil S/A a efetivar o(s) pagamentos acima relacionado(s), excetuando aquela(s) OB(s) indicada(s) para cancelamento.";
$pdf->text($pdf->getx(),$pdf->gety(),$texto_aut,0,4);

$pdf->ln(5);
$data_ger = strtotime($e87_dataproc);
$dataextenso = "Natal / RN, ".date('d',$data_ger)." de ".date('M', $data_ger)." de ".date('Y', $data_ger);
$pdf->text($pdf->getx(),$pdf->gety(),$dataextenso,0,4);
$pdf->ln(15);
$pdf->text(30,$pdf->gety(),"________________________________________________________",4);
$pdf->text(200,$pdf->gety(),"________________________________________________________",4);
$pdf->ln(5);
$pdf->text(60,$pdf->gety(),"Chefe USF",4);              
$pdf->text(225,$pdf->gety(),"Ordenador de Despesa",4);  

$tes =  "______________________________";
$pref =  "______________________________";

$largura = ( $pdf->w ) / 2;
$pdf->ln(10);
$pos = $pdf->gety();

}
$pdf->Output();
?>