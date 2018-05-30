create table plugins.empagemovtipotransmissaofatura (sequencial integer, 
                                                     empagemovtipotransmissao integer, 
                                                     fatura char(1));
create sequence plugins.empagemovtipotransmissaofatura_sequencial_seq;                                                     
alter table plugins.empagemovtipotransmissaofatura add primary key (sequencial);
                                                     
create table plugins.arquivoobngerado (sequencial integer,
                                       codgera integer,  
                                       datagera date);
create sequence plugins.arquivoobngerado_sequencial_seq;           
alter table plugins.arquivoobngerado add primary key (sequencial);
create index arquivoobngerado_datagera_in on plugins.arquivoobngerado (codgera);                             

create table plugins.empagedadosretarquivo (sequencial     integer ,
                                            empagedadosret integer ,
                                            usuario        integer ,
                                            data           date    ,
                                            arquivo        text    );
create sequence plugins.empagedadosretarquivo_sequencial_seq;
alter table plugins.empagedadosretarquivo add primary key (sequencial);
create index empagedadosretarquivo_empagedadosret_in on plugins.empagedadosretarquivo(empagedadosret);
create index empagedadosretarquivo_usuario_in on plugins.empagedadosretarquivo(usuario);
                                            
create table plugins.empagedadosretmovocorrenciaobservacao ( sequencial integer,                
                                                             empagedadosretmovocorrencia integer,
                                                             observacao text);
create sequence plugins.empagedadosretmovocorrenciaobservacao_sequencial_seq;  
alter table plugins.empagedadosretmovocorrenciaobservacao add primary key (sequencial);
create index empagedadosretmovocorrenciaobservacao_empagedadosretmovocorrencia_in on plugins.empagedadosretmovocorrenciaobservacao(empagedadosretmovocorrencia);   

create table plugins.tipopagamento (sequencial integer,
									  codtipo integer, 
                                      descricao varchar(20));
create sequence plugins.tipopagamento_sequencial_seq; 
alter table plugins.tipopagamento add primary key (sequencial);

insert into plugins.tipopagamento values (nextval('plugins.tipopagamento_sequencial_seq'), 1, 'GPS');
insert into plugins.tipopagamento values (nextval('plugins.tipopagamento_sequencial_seq'), 2, 'DARF');
insert into plugins.tipopagamento values (nextval('plugins.tipopagamento_sequencial_seq'), 3, 'DARF Simples');

create table plugins.empagemovpagamento (sequencial integer, 
                                                     empagemov integer not null, 
                                                     tipopagamento integer not null,
                                                     codreceita varchar(6),
													 codidentificacao varchar(2),   
													 periodoapuracao date,
													 datavencimento date,
													 mesanocompetencia varchar(7),
													 numreferencia numeric,
													 valorINSS numeric,          
													 valoroutras numeric,        
													 atualizacaomonetaria numeric,
													 valorreceitabruta numeric,
													 percentualreceita numeric,
													 valorprincipal numeric,
													 valormulta numeric,
													 jurosencargos numeric
													);
alter table plugins.empagemovpagamento add primary key (sequencial);
create index empagemovpagamento_empagemov_in on plugins.empagemovpagamento(empagemov);
create index empagemovpagamento_tipopagamento_in on plugins.empagemovpagamento(tipopagamento);
create index empagemovpagamento_codreceita_in on plugins.empagemovpagamento(codreceita);
create sequence plugins.empagemovpagamento_sequencial_seq; 

create table plugins.empagemovdetalhetransmissaoautenticacao (sequencial integer,
									  							empagemovdetalhetransmissao integer, 
                                      							numautenticacao varchar(16));
alter table plugins.empagemovdetalhetransmissaoautenticacao add primary key (sequencial);                                      							
create sequence plugins.empagemovdetalhetransmissaoautenticacao_sequencial_seq; 

create table plugins.empagemovpagamentoautenticacao (sequencial integer,
									  							empagemovpagamento integer, 
                                      							numautenticacao varchar(16));
alter table plugins.empagemovpagamentoautenticacao add primary key(sequencial);
alter table plugins.empagemovpagamentoautenticacao add foreign key (empagemovpagamento) references plugins.empagemovpagamento (sequencial);                                      							
create sequence plugins.empagemovpagamentoautenticacao_sequencial_seq; 

create table plugins.saltesfinalidadepagamento (saltes integer);
alter table plugins.saltesfinalidadepagamento add primary key (saltes);


--insert into configuracoes.db_itensmenu (id_item   ,
--                          descricao ,
--                          help      ,
--                          funcao    ,
--                          itemativo ,
--                          manutencao,
--                          desctec   ,
--                          libcliente)
--                  values (nextval('configuracoes.db_itensmenu_id_item_seq'), 
--                          'Gerar Arquivo TXT - Unidades', 
--                          'Geração de Arquivo TXT por Unidades - plugin', 
--                          'cai4_emissaoarquivoobn001.php', 
--                          1, 
--                          1, 
--                          'Plugin: GeracaoArquivoOBN', 
--                          true);

--insert into configuracoes.db_menu ( id_item, 
--                      id_item_filho, 
--                      menusequencia, 
--                      modulo ) 
--             values (4343, 
--                     currval('configuracoes.db_itensmenu_id_item_seq'), 
--                     18, 
--                     39);
                                                             
