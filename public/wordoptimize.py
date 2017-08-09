# coding=utf-8
import thulac
import argparse
import json
# word_type = {
#     "n": "名词", "np": "人名", "ns": "地名", "ni": "机构名", "nz": "其它专名",
#     "m": "数词", "q": "量词", "mq": "数量词", "t": "时间词", "f": "方位词", "s": "处所词",
#     "v": "动词", "a": "形容词", "d": "副词", "h": "前接成分", "k": "后接成分",
#     "i": "习语", "j": "简称", "r": "代词", "c": "连词", "p": "介词", "u": "助词", "y": "语气助词",
#     "e": "叹词", "o": "拟声词", "g": "语素", "w": "标点", "x": "其它"
# }

parser = argparse.ArgumentParser()
parser.add_argument('--word', type=str, help="需要优化的词")
args = parser.parse_args()

thu1 = thulac.thulac()

text = thu1.cut('欧莱雅男士控油炭爽双效洁面膏100ml')

result = []
for (word, type) in text:
    if type == 'n':
        result.append(word)

print json.dumps(result)