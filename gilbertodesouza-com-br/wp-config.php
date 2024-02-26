<?php
/**
 * As configurações básicas do WordPress
 *
 * O script de criação wp-config.php usa esse arquivo durante a instalação.
 * Você não precisa usar o site, você pode copiar este arquivo
 * para "wp-config.php" e preencher os valores.
 *
 * Este arquivo contém as seguintes configurações:
 *
 * * Configurações do MySQL
 * * Chaves secretas
 * * Prefixo do banco de dados
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Configurações do MySQL - Você pode pegar estas informações com o serviço de hospedagem ** //
/** O nome do banco de dados do WordPress */
define( 'WP_CACHE', true );
define( 'DB_NAME', 'gilbertodesouza_wp' );

/** Usuário do banco de dados MySQL */
define( 'DB_USER', 'gilbertodesouza' );

/** Senha do banco de dados MySQL */
define( 'DB_PASSWORD', '*QQ9JC1*t3N^=rU@' );

/** Nome do host do MySQL */
define( 'DB_HOST', '127.0.0.1' );

/** Charset do banco de dados a ser usado na criação das tabelas. */
define( 'DB_CHARSET', 'utf8mb4' );

/** O tipo de Collate do banco de dados. Não altere isso se tiver dúvidas. */
define( 'DB_COLLATE', '' );

/**#@+
 * Chaves únicas de autenticação e salts.
 *
 * Altere cada chave para um frase única!
 * Você pode gerá-las
 * usando o {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org
 * secret-key service}
 * Você pode alterá-las a qualquer momento para invalidar quaisquer
 * cookies existentes. Isto irá forçar todos os
 * usuários a fazerem login novamente.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '|arbn[juHx<q,:71A9E/vdM@`~w|E`]s`o(kD>*&GA1X%uwf7kZt1V*tO9@ mk0_' );
define( 'SECURE_AUTH_KEY',  'ax</oPIoR,Uv.<$_9jsFiskFpEwkMfSl@|t&5IF]f:DYXvYz{HNq+-O=Rb_{eagk' );
define( 'LOGGED_IN_KEY',    'S0 R6nfQY25VIqgf#REHVp1Jyv*rUeWOtffQA}F6q@O~#t1k{B[U>z5F2zCRePs;' );
define( 'NONCE_KEY',        'T{C]Dls6&&;}[}y5M*0h+GF${yYtHXpN(X+ (j3XNS33*-*&m6B>Ug&L?(aWbosG' );
define( 'AUTH_SALT',        ')@,4#a(OIGA[u%/.LPS7%#&TF|1g.y1c]].DQ ZBi[ZI(!3F$1-h@W0Db}7X3VCc' );
define( 'SECURE_AUTH_SALT', 'NPVkJ!hT2@s|Jb[>CO+>Idy>`=!_>,#]y8=G$gI>b!EJii_upd cW3~GH7f1&Xwm' );
define( 'LOGGED_IN_SALT',   'I`:q DAD_OP~/4UQ[W`,:zbZ]5C4RzM{<u3P w5C/QNs-~pER~J:>Sh{19?Wj18#' );
define( 'NONCE_SALT',       '5Ys339<,)v%od<ZM&6jJgV0],o?|^vv=`XH7{;SlMbayPINn IRK;nl[bhI!z oy' );

/**#@-*/

/**
 * Prefixo da tabela do banco de dados do WordPress.
 *
 * Você pode ter várias instalações em um único banco de dados se você der
 * um prefixo único para cada um. Somente números, letras e sublinhados!
 */
$table_prefix = 'wp_';

/**
 * Para desenvolvedores: Modo de debug do WordPress.
 *
 * Altere isto para true para ativar a exibição de avisos
 * durante o desenvolvimento. É altamente recomendável que os
 * desenvolvedores de plugins e temas usem o WP_DEBUG
 * em seus ambientes de desenvolvimento.
 *
 * Para informações sobre outras constantes que podem ser utilizadas
 * para depuração, visite o Codex.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Isto é tudo, pode parar de editar! :) */

/** Caminho absoluto para o diretório WordPress. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname(__FILE__) . '/' );
}

/** Configura as variáveis e arquivos do WordPress. */
require_once ABSPATH . 'wp-settings.php';