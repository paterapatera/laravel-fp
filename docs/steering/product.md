# Product Overview

Laravel ベースの Web アプリケーション開発基盤。現時点ではスケルトン段階で、Kiro 形式の仕様駆動開発（Spec-Driven Development）と AI エージェントによる実装ワークフローが組み込まれている。

## Core Capabilities

- **Web アプリケーション基盤**: ルーティング、認証（User モデル）、セッション、キュー、マイグレーションなど Laravel 標準機能
- **仕様駆動開発**: `docs/specs/` で機能ごとに要件・設計・タスクを管理し、段階的承認のうえ実装
- **エージェント連携開発**: `AGENTS.md` と `docs/steering/` をプロジェクトメモリとして、Cursor 等の AI エージェントが一貫した規約で実装
- **フロントエンド**: Vite + Tailwind CSS v4 によるアセットビルドと Blade ビュー

## Target Use Cases

- 新規機能を仕様（requirements → design → tasks）から実装する開発フロー
- 複数エージェント・サブエージェントによる自律的タスク実装とレビューゲート
- Sail 上でのローカル開発と PHPUnit による検証

## Value Proposition

- Laravel の慣習に沿った予測可能な構造で、AI エージェントが安全に変更を加えやすい
- 仕様とステアリングを分離し、プロジェクト全体の原則（steering）と機能単位の詳細（specs）を明確化
- Laravel Boost により、フレームワーク固有のベストプラクティスと MCP ツールが開発に統合済み

---
_Focus on patterns and purpose, not exhaustive feature lists_
