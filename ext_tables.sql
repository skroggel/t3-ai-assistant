#
# Table structure for table fields derived from TCA
#

CREATE TABLE `tx_aiassistant_connection_ai` (
    `title` varchar(255) DEFAULT '' NOT NULL,
    `connector_identifier` varchar(255) DEFAULT 'openai' NOT NULL,
    `base_url` varchar(512) DEFAULT '' NOT NULL,
    `api_key` varchar(1024) DEFAULT '' NOT NULL,
    `organization` varchar(255) DEFAULT '' NOT NULL,
    `project` varchar(255) DEFAULT '' NOT NULL,
    `default_model` varchar(255) DEFAULT '' NOT NULL,
    `default_temperature` double DEFAULT '0.2' NOT NULL,
    `embedding_model` varchar(255) DEFAULT '' NOT NULL,
    `embedding_temperature` double DEFAULT '0' NOT NULL,
    `additional_options` text
);

CREATE TABLE `tx_aiassistant_assistant_profile` (
    `title` varchar(255) DEFAULT '' NOT NULL,
    `assistant_label` varchar(255) DEFAULT '' NOT NULL,
    `ai_connection` int(11) unsigned DEFAULT '0' NOT NULL,
    `vector_store_connection` int(11) unsigned DEFAULT '0' NOT NULL,
    `intro_text` text,
    `initial_message` text,
    `identity_prompt` text,
    `behavior_rules` text,
    `retrieval_rules` text,
    `output_rules` text,
);

CREATE TABLE `tx_aiassistant_assistant_pipeline_step` (
    `assistant_profile` int(11) unsigned DEFAULT '0' NOT NULL,
    `title` varchar(255) DEFAULT '' NOT NULL,
    `type` varchar(255) DEFAULT '' NOT NULL,
    `processor_identifier` varchar(255) DEFAULT '' NOT NULL,
    `stage` varchar(255) DEFAULT 'retrieval' NOT NULL,
    `include_identity_prompt` tinyint(4) unsigned DEFAULT '1' NOT NULL,
    `include_behavior_rules` tinyint(4) unsigned DEFAULT '0' NOT NULL,
    `include_retrieval_rules` tinyint(4) unsigned DEFAULT '1' NOT NULL,
    `include_output_rules` tinyint(4) unsigned DEFAULT '0' NOT NULL,
    `step_identity` text,
    `step_behavior_rules` text,
    `step_retrieval_rules` text,
    `step_output_rules` text,
    `history_mode` varchar(255) DEFAULT 'last_n' NOT NULL,
    `history_limit` int(11) DEFAULT '5' NOT NULL,
    `model` varchar(255) DEFAULT '' NOT NULL,
    `temperature` double DEFAULT '0' NOT NULL,
    `max_tokens` int(11) DEFAULT '500' NOT NULL,
    `max_retrieval_results` int(11) DEFAULT '8' NOT NULL,
    `retrieval_vector_store_connection` int(11) unsigned DEFAULT '0' NOT NULL,
    `retrieval_collection` varchar(255) DEFAULT '' NOT NULL,
    `score_threshold` double DEFAULT '0' NOT NULL,
    `max_context_chunks` int(11) DEFAULT '6' NOT NULL,
    `max_context_characters` int(11) DEFAULT '9000' NOT NULL,
    `prompt_metadata_fields` varchar(255) DEFAULT '' NOT NULL,
    `failure_strategy` varchar(255) DEFAULT 'continue' NOT NULL
);

CREATE TABLE `tx_aiassistant_pipeline_trace` (
    `level` varchar(16) DEFAULT '' NOT NULL,
    `event_name` varchar(255) DEFAULT '' NOT NULL,
    `route` varchar(255) DEFAULT '' NOT NULL,
    `chat_identifier` varchar(255) DEFAULT '' NOT NULL,
    `trace_id` varchar(255) DEFAULT '' NOT NULL,
    `step_title` varchar(255) DEFAULT '' NOT NULL,
    `processor_type` varchar(64) DEFAULT '' NOT NULL,
    `duration_ms` int(11) DEFAULT '0' NOT NULL,
    `query_text` text,
    `payload` text
);

CREATE TABLE `tx_aiassistant_indexer_connector` (
    `title` varchar(255) DEFAULT '' NOT NULL,
    `type` varchar(255) DEFAULT '' NOT NULL,
    `base_url` varchar(512) DEFAULT '' NOT NULL,
    `download_base_url` varchar(512) DEFAULT '' NOT NULL,
    `download_path` varchar(512) DEFAULT '' NOT NULL,
    `client_id` varchar(512) DEFAULT '' NOT NULL,
    `client_secret` varchar(512) DEFAULT '' NOT NULL,
    `verify_tls` tinyint(4) unsigned DEFAULT '1' NOT NULL,
    `lookback_days` int(11) DEFAULT '1' NOT NULL,
    `custom_fields` text,
    `indexed_fields` text
);

CREATE TABLE `tx_aiassistant_indexer_run` (
    `source_type` varchar(50) DEFAULT '' NOT NULL,
    `indexer_uid` int(11) DEFAULT '0' NOT NULL,
    `status` varchar(16) DEFAULT '' NOT NULL,
    `is_dry_run` tinyint(4) unsigned DEFAULT '0' NOT NULL,
    `started_at` int(11) DEFAULT '0' NOT NULL,
    `finished_at` int(11) DEFAULT '0' NOT NULL,
    `items_processed` int(11) DEFAULT '0' NOT NULL,
    `items_indexed` int(11) DEFAULT '0' NOT NULL,
    `items_skipped` int(11) DEFAULT '0' NOT NULL,
    `items_failed` int(11) DEFAULT '0' NOT NULL,
    `items_removed` int(11) DEFAULT '0' NOT NULL,
    `chunks_total` int(11) DEFAULT '0' NOT NULL,
    `message` text
);

CREATE TABLE `tx_aiassistant_indexer_source` (
    `source_type` varchar(50) DEFAULT '' NOT NULL,
    `indexer_uid` int(11) DEFAULT '0' NOT NULL,
    `source_id` varchar(1024) DEFAULT '' NOT NULL,
    `source_hash` varchar(64) DEFAULT '' NOT NULL,
    `language` int(11) DEFAULT '0' NOT NULL,
    `collection` varchar(255) DEFAULT '' NOT NULL,
    `vector_store_connection` int(11) DEFAULT '0' NOT NULL,
    `page_id` int(11) DEFAULT '0' NOT NULL,
    `path` varchar(1024) DEFAULT '' NOT NULL,
    `filename` varchar(255) DEFAULT '' NOT NULL,
    `content_checksum` varchar(64) DEFAULT '' NOT NULL,
    `storage_source_hash` varchar(64) DEFAULT '' NOT NULL,
    `file_mtime` int(11) DEFAULT '0' NOT NULL,
    `file_ctime` int(11) DEFAULT '0' NOT NULL,
    `last_indexed` int(11) DEFAULT '0' NOT NULL,
    `last_changed` int(11) DEFAULT '0' NOT NULL,
    `status` varchar(16) DEFAULT '' NOT NULL,
    `locked_until` int(11) DEFAULT '0' NOT NULL,
    `lock_token` varchar(64) DEFAULT '' NOT NULL,
    `last_error` text
);

CREATE TABLE `tx_aiassistant_indexer` (
    `title` varchar(255) DEFAULT '' NOT NULL,
    `type` varchar(255) DEFAULT '' NOT NULL,
    `indexer_identifier` varchar(255) DEFAULT '' NOT NULL,
    `adapter_identifier` varchar(255) DEFAULT '' NOT NULL,
    `additional_metadata` text,
    `ai_connection` int(11) unsigned DEFAULT '0' NOT NULL,
    `vector_store_connection` int(11) unsigned DEFAULT '0' NOT NULL,
    `collection` varchar(255) DEFAULT '' NOT NULL,
    `connector_uid` int(11) unsigned DEFAULT '0' NOT NULL,
    `shopware_base_url` varchar(512) DEFAULT '' NOT NULL,
    `shopware_download_base_url` varchar(512) DEFAULT '' NOT NULL,
    `shopware_download_path` varchar(512) DEFAULT '' NOT NULL,
    `shopware_client_id` varchar(512) DEFAULT '' NOT NULL,
    `shopware_api_key` varchar(1024) DEFAULT '' NOT NULL,
    `shopware_verify_tls` tinyint(4) unsigned DEFAULT '1' NOT NULL,
    `shopware_lookback_days` int(11) DEFAULT '1' NOT NULL,
    `shopware_custom_fields` text,
    `shopware_indexed_fields` text,
    `chunk_size` int(11) DEFAULT '0' NOT NULL,
    `chunk_overlap` int(11) DEFAULT '0' NOT NULL,
    `max_chunks` int(11) DEFAULT '0' NOT NULL,
    `min_chunk_chars` int(11) DEFAULT '0' NOT NULL,
    `import_path` text,
    `include_subfolders` tinyint(4) unsigned DEFAULT '0' NOT NULL,
    `root_pages` text,
    `page_fields` text,
    `content_types` text,
    `content_fields` text,
    `additional_content_fields` text
);

CREATE TABLE `tx_aiassistant_indexer_shopware_state` (
    `connector_uid` int(11) DEFAULT '0' NOT NULL,
    `indexer_uid` int(11) DEFAULT '0' NOT NULL,
    `cursor_updated_at` int(11) DEFAULT '0' NOT NULL,
    `cursor_source_id` varchar(64) DEFAULT '' NOT NULL,
    `cleanup_cursor_source_id` varchar(64) DEFAULT '' NOT NULL,
    `cleanup_last_run_at` int(11) DEFAULT '0' NOT NULL,
    `last_run_started_at` int(11) DEFAULT '0' NOT NULL,
    `last_run_finished_at` int(11) DEFAULT '0' NOT NULL,
    `status` varchar(16) DEFAULT '' NOT NULL,
    `last_error` text,
    `items_processed` int(11) DEFAULT '0' NOT NULL,
    `items_indexed` int(11) DEFAULT '0' NOT NULL,
    `items_skipped` int(11) DEFAULT '0' NOT NULL,
    `items_failed` int(11) DEFAULT '0' NOT NULL
);

CREATE TABLE `tx_aiassistant_connection_vector_database` (
    `title` varchar(255) DEFAULT '' NOT NULL,
    `connector_identifier` varchar(255) DEFAULT 'qdrant' NOT NULL,
    `endpoint` varchar(512) DEFAULT '' NOT NULL,
    `api_key` varchar(1024) DEFAULT '' NOT NULL,
    `default_collection` varchar(255) DEFAULT '' NOT NULL,
    `collections` text,
    `vector_size` int(11) DEFAULT '1536' NOT NULL,
    `distance` varchar(255) DEFAULT 'Cosine' NOT NULL,
    `additional_options` text
);
