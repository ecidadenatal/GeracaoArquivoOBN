drop table plugins.empagemovtipotransmissaofatura;
drop sequence plugins.empagemovtipotransmissaofatura_sequencial_seq;                                                     
                                                     
drop table plugins.arquivoobngerado;
drop sequence plugins.arquivoobngerado_sequencial_seq;                                        

drop table plugins.empagedadosretarquivo;
drop sequence plugins.empagedadosretarquivo_sequencial_seq;                                            
                                            
drop table plugins.empagedadosretmovocorrenciaobservacao;
drop sequence plugins.empagedadosretmovocorrenciaobservacao_sequencial_seq;

drop table if exists empenhonotacontroleinternohistorico;
drop sequence if exists empenhonotacontroleinterno_sequencial_seq;
drop table if exists empenhonotacontroleinterno;

drop table if exists plugins.liquidacaocompetencia;
drop sequence if exists plugins.liquidacaocompetencia_sequencial_seq;                                              

drop table if exists plugins.empenhonotacontroleinternohistoricousuario;
drop sequence if exists plugins.empenhonotacontroleinternousuario_sequencial_seq;

drop table plugins.tipopagamento;
drop sequence plugins.tipopagamento_sequencial_seq; 

drop table plugins.empagemovpagamento;
drop sequence plugins.empagemovpagamento_sequencial_seq; 

drop table plugins.empagemovdetalhetransmissaoautenticacao;
drop sequence plugins.empagemovdetalhetransmissaoautenticacao_sequencial_seq; 

drop table plugins.empagemovpagamentoautenticacao;
drop sequence plugins.empagemovpagamentoautenticacao_sequencial_seq; 

drop table plugins.conhistgeracaoobn;


--Menus
--delete from configuracoes.db_menu   
--      using configuracoes.db_itensmenu 
--      where db_itensmenu.id_item = db_menu.id_item_filho 
--        and db_itensmenu.desctec = 'Plugin: GeracaoArquivoOBN';
--
--delete from configuracoes.db_permissao 
--      using configuracoes.db_itensmenu 
--      where db_itensmenu.id_item = db_permissao.id_item 
--        and db_itensmenu.desctec = 'Plugin: GeracaoArquivoOBN';
--
--delete from configuracoes.db_itensmenu 
--      where db_itensmenu.desctec = 'Plugin: GeracaoArquivoOBN';

                                                             